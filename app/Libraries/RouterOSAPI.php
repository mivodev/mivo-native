<?php

namespace App\Libraries;

/*****************************
 *
 * RouterOS PHP API class v1.6 (Ported to MIVO)
 * Original Author: Denis Basta
 * Ported by: MIVO Team
 *
 ******************************/

class RouterOSAPI
{
    public $debug = false; //  Show debug information

    public $connected = false; //  Connection state

    public $port = 8728;  //  Port to connect to (default 8729 for ssl)

    public $ssl = false; //  Connect using SSL (must enable api-ssl in IP/Services)

    public $timeout = 3;     //  Connection attempt timeout and data read timeout

    public $attempts = 5;     //  Connection attempt count

    public $delay = 3;     //  Delay between connection attempts in seconds

    public $socket;            //  Variable for storing socket resource

    public $error_no;          //  Variable for storing connection error number, if any

    public $error_str;         //  Variable for storing connection error text, if any

    public function __construct()
    {
        // Constructor logic if needed
    }

    public function isIterable($var)
    {
        return $var !== null
                && (is_array($var)
                || $var instanceof \Traversable
                || $var instanceof \Iterator
                || $var instanceof \IteratorAggregate
                );
    }

    public function debug($text)
    {
        if ($this->debug) {
            echo $text."\n";
        }
    }

    public function encodeLength($length)
    {
        if ($length < 0x80) {
            $length = chr($length);
        } elseif ($length < 0x4000) {
            $length |= 0x8000;
            $length = chr(($length >> 8) & 0xFF).chr($length & 0xFF);
        } elseif ($length < 0x200000) {
            $length |= 0xC00000;
            $length = chr(($length >> 16) & 0xFF).chr(($length >> 8) & 0xFF).chr($length & 0xFF);
        } elseif ($length < 0x10000000) {
            $length |= 0xE0000000;
            $length = chr(($length >> 24) & 0xFF).chr(($length >> 16) & 0xFF).chr(($length >> 8) & 0xFF).chr($length & 0xFF);
        } elseif ($length >= 0x10000000) {
            $length = chr(0xF0).chr(($length >> 24) & 0xFF).chr(($length >> 16) & 0xFF).chr(($length >> 8) & 0xFF).chr($length & 0xFF);
        }

        return $length;
    }

    public function connect($ip, $login, $password)
    {
        for ($ATTEMPT = 1; $ATTEMPT <= $this->attempts; $ATTEMPT++) {
            $this->connected = false;
            $PROTOCOL = ($this->ssl ? 'ssl://' : '');
            $context = stream_context_create(['ssl' => ['ciphers' => 'ADH:ALL', 'verify_peer' => false, 'verify_peer_name' => false]]);
            $this->debug('Connection attempt #'.$ATTEMPT.' to '.$PROTOCOL.$ip.':'.$this->port.'...');
            $this->socket = @stream_socket_client($PROTOCOL.$ip.':'.$this->port, $this->error_no, $this->error_str, $this->timeout, STREAM_CLIENT_CONNECT, $context);
            if ($this->socket) {
                stream_set_timeout($this->socket, $this->timeout);
                $this->write('/login', false);
                $this->write('=name='.$login, false);
                $this->write('=password='.$password);
                $RESPONSE = $this->read(false);
                if (isset($RESPONSE[0])) {
                    if ($RESPONSE[0] == '!done') {
                        if (! isset($RESPONSE[1])) {
                            // Login method post-v6.43
                            $this->connected = true;
                            break;
                        } else {
                            // Login method pre-v6.43
                            $MATCHES = [];
                            if (preg_match_all('/[^=]+/i', $RESPONSE[1], $MATCHES)) {
                                if ($MATCHES[0][0] == 'ret' && strlen($MATCHES[0][1]) == 32) {
                                    $this->write('/login', false);
                                    $this->write('=name='.$login, false);
                                    $this->write('=response=00'.md5(chr(0).$password.pack('H*', $MATCHES[0][1])));
                                    $RESPONSE = $this->read(false);
                                    if (isset($RESPONSE[0]) && $RESPONSE[0] == '!done') {
                                        $this->connected = true;
                                        break;
                                    }
                                }
                            }
                        }
                    }
                }
                fclose($this->socket);
            }
            sleep($this->delay);
        }

        if ($this->connected) {
            $this->debug('Connected...');
        } else {
            $this->debug('Error...');
        }

        return $this->connected;
    }

    public function disconnect()
    {
        if (is_resource($this->socket)) {
            fclose($this->socket);
        }
        $this->connected = false;
        $this->debug('Disconnected...');
    }

    public function parseResponse($response)
    {
        if (is_array($response)) {
            $PARSED = [];
            $CURRENT = null;
            $singlevalue = null;
            foreach ($response as $x) {
                if (in_array($x, ['!fatal', '!re', '!trap'])) {
                    if ($x == '!re') {
                        $CURRENT = &$PARSED[];
                    } else {
                        $CURRENT = &$PARSED[$x][];
                    }
                } elseif ($x != '!done') {
                    $MATCHES = [];
                    if (preg_match_all('/[^=]+/i', $x, $MATCHES)) {
                        if ($MATCHES[0][0] == 'ret') {
                            $singlevalue = $MATCHES[0][1];
                        }
                        $CURRENT[$MATCHES[0][0]] = (isset($MATCHES[0][1]) ? $MATCHES[0][1] : '');
                    }
                }
            }

            if (empty($PARSED) && ! is_null($singlevalue)) {
                $PARSED = $singlevalue;
            }

            return $PARSED;
        } else {
            return [];
        }
    }

    public function arrayChangeKeyName(&$array)
    {
        if (is_array($array)) {
            foreach ($array as $k => $v) {
                $tmp = str_replace('-', '_', $k);
                $tmp = str_replace('/', '_', $tmp);
                if ($tmp) {
                    $array_new[$tmp] = $v;
                } else {
                    $array_new[$k] = $v;
                }
            }

            return $array_new;
        } else {
            return $array;
        }
    }

    public function read($parse = true)
    {
        $RESPONSE = [];
        $receiveddone = false;
        while (true) {
            $BYTE = ord(fread($this->socket, 1));
            $LENGTH = 0;
            if ($BYTE & 128) {
                if (($BYTE & 192) == 128) {
                    $LENGTH = (($BYTE & 63) << 8) + ord(fread($this->socket, 1));
                } else {
                    if (($BYTE & 224) == 192) {
                        $LENGTH = (($BYTE & 31) << 8) + ord(fread($this->socket, 1));
                        $LENGTH = ($LENGTH << 8) + ord(fread($this->socket, 1));
                    } else {
                        if (($BYTE & 240) == 224) {
                            $LENGTH = (($BYTE & 15) << 8) + ord(fread($this->socket, 1));
                            $LENGTH = ($LENGTH << 8) + ord(fread($this->socket, 1));
                            $LENGTH = ($LENGTH << 8) + ord(fread($this->socket, 1));
                        } else {
                            $LENGTH = ord(fread($this->socket, 1));
                            $LENGTH = ($LENGTH << 8) + ord(fread($this->socket, 1));
                            $LENGTH = ($LENGTH << 8) + ord(fread($this->socket, 1));
                            $LENGTH = ($LENGTH << 8) + ord(fread($this->socket, 1));
                        }
                    }
                }
            } else {
                $LENGTH = $BYTE;
            }

            $_ = '';

            if ($LENGTH > 0) {
                $_ = '';
                $retlen = 0;
                while ($retlen < $LENGTH) {
                    $toread = $LENGTH - $retlen;
                    $_ .= fread($this->socket, $toread);
                    $retlen = strlen($_);
                }
                $RESPONSE[] = $_;
                $this->debug('>>> ['.$retlen.'/'.$LENGTH.'] bytes read.');
            }

            if ($_ == '!done') {
                $receiveddone = true;
            }

            $STATUS = stream_get_meta_data($this->socket);
            if ($LENGTH > 0) {
                $this->debug('>>> ['.$LENGTH.', '.$STATUS['unread_bytes'].']'.$_);
            }

            if ((! $this->connected && ! $STATUS['unread_bytes']) || ($this->connected && ! $STATUS['unread_bytes'] && $receiveddone)) {
                break;
            }
        }

        if ($parse) {
            $RESPONSE = $this->parseResponse($RESPONSE);
        }

        return $RESPONSE;
    }

    public function write($command, $param2 = true)
    {
        if ($command) {
            $data = explode("\n", $command);
            foreach ($data as $com) {
                $com = trim($com);
                fwrite($this->socket, $this->encodeLength(strlen($com)).$com);
                $this->debug('<<< ['.strlen($com).'] '.$com);
            }

            if (gettype($param2) == 'integer') {
                fwrite($this->socket, $this->encodeLength(strlen('.tag='.$param2)).'.tag='.$param2.chr(0));
                $this->debug('<<< ['.strlen('.tag='.$param2).'] .tag='.$param2);
            } elseif (gettype($param2) == 'boolean') {
                fwrite($this->socket, ($param2 ? chr(0) : ''));
            }

            return true;
        } else {
            return false;
        }
    }

    public function comm($com, $arr = [])
    {
        $count = count($arr);
        $this->write($com, ! $arr);
        $i = 0;
        if ($this->isIterable($arr)) {
            foreach ($arr as $k => $v) {
                switch ($k[0]) {
                    case '?':
                        $el = "$k=$v";
                        break;
                    case '~':
                        $el = "$k~$v";
                        break;
                    default:
                        $el = "=$k=$v";
                        break;
                }

                $last = ($i++ == $count - 1);
                $this->write($el, $last);
            }
        }

        return $this->read();
    }

    public function __destruct()
    {
        $this->disconnect();
    }

    // Helpers included in original file
    public static function encrypt($string, $key = 128)
    {
        $result = '';
        for ($i = 0, $k = strlen($string); $i < $k; $i++) {
            $char = substr($string, $i, 1);
            $keychar = substr($key, ($i % strlen($key)) - 1, 1);
            $char = chr(ord($char) + ord($keychar));
            $result .= $char;
        }

        return base64_encode($result);
    }

    public static function decrypt($string, $key = 128)
    {
        $result = '';
        $string = base64_decode($string);
        for ($i = 0, $k = strlen($string); $i < $k; $i++) {
            $char = substr($string, $i, 1);
            $keychar = substr($key, ($i % strlen($key)) - 1, 1);
            $char = chr(ord($char) - ord($keychar));
            $result .= $char;
        }

        return $result;
    }
}
