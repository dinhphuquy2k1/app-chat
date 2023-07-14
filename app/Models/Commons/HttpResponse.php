<?php

namespace App\Models\Commons;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HttpResponse extends Model
{
    use HasFactory;

    /**
     * HTTP status codes
     *
     * @link https://gist.github.com/henriquemoody/6580488
     *
     * @var []
     */
    private $code = array(
        100 => 'Continue',
        101 => 'Switching Protocols',
        102 => 'Processing', // WebDAV; RFC 2518
        200 => 'OK',
        201 => 'Created',
        202 => 'Accepted',
        203 => 'Non-Authoritative Information', // since HTTP/1.1
        204 => 'No Content',
        205 => 'Reset Content',
        206 => 'Partial Content',
        207 => 'Multi-Status', // WebDAV; RFC 4918
        208 => 'Already Reported', // WebDAV; RFC 5842
        226 => 'IM Used', // RFC 3229
        300 => 'Multiple Choices',
        301 => 'Moved Permanently',
        302 => 'Found',
        303 => 'See Other', // since HTTP/1.1
        304 => 'Not Modified',
        305 => 'Use Proxy', // since HTTP/1.1
        306 => 'Switch Proxy',
        307 => 'Temporary Redirect', // since HTTP/1.1
        308 => 'Permanent Redirect', // approved as experimental RFC
        400 => 'Bad Request',
        401 => 'Unauthorized',
        402 => 'Payment Required',
        403 => 'Forbidden',
        404 => 'Not Found',
        405 => 'Method Not Allowed',
        406 => 'Not Acceptable',
        407 => 'Proxy Authentication Required',
        408 => 'Request Timeout',
        409 => 'Conflict',
        410 => 'Gone',
        411 => 'Length Required',
        412 => 'Precondition Failed',
        413 => 'Request Entity Too Large',
        414 => 'Request-URI Too Long',
        415 => 'Unsupported Media Type',
        416 => 'Requested Range Not Satisfiable',
        417 => 'Expectation Failed',
        418 => 'I\'m a teapot', // RFC 2324
        419 => 'Authentication Timeout', // not in RFC 2616
        420 => 'Enhance Your Calm', // Twitter
        420 => 'Method Failure', // Spring Framework
        422 => 'Unprocessable Entity', // WebDAV; RFC 4918
        423 => 'Locked', // WebDAV; RFC 4918
        424 => 'Failed Dependency', // WebDAV; RFC 4918
        424 => 'Method Failure', // WebDAV)
        425 => 'Unordered Collection', // Internet draft
        426 => 'Upgrade Required', // RFC 2817
        428 => 'Precondition Required', // RFC 6585
        429 => 'Too Many Requests', // RFC 6585
        431 => 'Request Header Fields Too Large', // RFC 6585
        444 => 'No Response', // Nginx
        449 => 'Retry With', // Microsoft
        450 => 'Blocked by Windows Parental Controls', // Microsoft
        451 => 'Redirect', // Microsoft
        451 => 'Unavailable For Legal Reasons', // Internet draft
        494 => 'Request Header Too Large', // Nginx
        495 => 'Cert Error', // Nginx
        496 => 'No Cert', // Nginx
        497 => 'HTTP to HTTPS', // Nginx
        499 => 'Client Closed Request', // Nginx
        500 => 'Internal Server Error',
        501 => 'Not Implemented',
        502 => 'Bad Gateway',
        503 => 'Service Unavailable',
        504 => 'Gateway Timeout',
        505 => 'HTTP Version Not Supported',
        506 => 'Variant Also Negotiates', // RFC 2295
        507 => 'Insufficient Storage', // WebDAV; RFC 4918
        508 => 'Loop Detected', // WebDAV; RFC 5842
        509 => 'Bandwidth Limit Exceeded', // Apache bw/limited extension
        510 => 'Not Extended', // RFC 2774
        511 => 'Network Authentication Required', // RFC 6585
        598 => 'Network read timeout error', // Unknown
        599 => 'Network connect timeout error', // Unknown
    );

    /**
     *
     * @var int
     */
    private $status = 200;

    /**
     *
     * @var []
     */
    private $headers = [];

    /**
     *
     * @var string
     */
    private $content;

    /**
     *
     * @param string  $url
     * @param array $params
     * @param bool $session
     * @param int $status
     */
    public function redirect($url, array $params = [], $session = false, $status = 200)
    {
        //TODO
        // $params & $session

        if (!isset($this->code[$status])) {
            $status = 404;
        }

        header("HTTP/1.1 ${status} " . $this->code[$status]);

        header("Location: " . $url);
    }

    /**
     *
     * @param  int $key
     * @param string $value
     * @return $this
     */
    public function setHeader($key, $value)
    {
        $this->headers[$key] = $value;
        return $this;
    }

    public function getHeader(){
        return $this->headers;
    }

    public function getCode(){
        return $this->code;
    }

    /**
     *
     * @param int $status
     * @return $this
     */
    public function setStatus($status)
    {
        $this->status = $status;

        return $this;
    }

    /**
     *
     * @param string $content
     * @return $this
     */
    public function setContent($content)
    {
        $this->content = $content;
        return $this;
    }

    public function getContent(){
        return $this->content;
    }


    public static function success($data, $message = 'Success', $status = 200)
    {
        return response()->json([
            'app-auth' => \Auth::user(),
            'success' => true,
            'result' => $data,
            'message' => $message,
        ], $status);
    }

    public static function error($message = 'Error', $status = 400)
    {
        return response()->json([
            'app-auth' => \Auth::user(),
            'success' => false,
            'message' => $message,
        ], $status);
    }

    /**
     * Send the headers
     *
     */
    public function send()
    {
        if (!isset($this->code[$this->status])) {
            $status = 404;
        } else {
            $status = $this->status;
        }
        header('HTTP/1.1 ' . $status . ' ' . $this->code[$status]);
        foreach ($this->headers as $key => $value) {
            header($key . ': ' . $value);
        }

        return response()->json([
            'data' => $this->content
        ], $status);
    }
}
