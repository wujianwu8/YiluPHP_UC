<?php
/*
 * CURL方法类
 * YiluPHP vision 1.0
 * User: Jim.Wu
 * Date: 19/10/08
 * Time: 22:55
 */

class curl
{
    private $_ch;
    private $response;

    // config from config.php
    public $options;

    // default config
    private $_config = array(
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HEADER         => true,
        CURLOPT_VERBOSE        => true,
        CURLOPT_AUTOREFERER    => true,         
        CURLOPT_CONNECTTIMEOUT => 30,
        CURLOPT_TIMEOUT        => 30,
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_HTTP_VERSION   => CURL_HTTP_VERSION_1_0,
        CURLOPT_USERAGENT => 'Mozilla/5.0 (Windows NT 6.1; Win64; x64; rv:5.0) Gecko/20110619 Firefox/5.0'
    );

    public function init()
    {
        try {
            $this->_ch = curl_init();
            $options = is_array($this->options) ? ($this->options + $this->_config) : $this->_config;
            $this->setOptions($options);
        } catch (\Exception $e) {
            throw new \Exception('Curl not installed');
        }
    }

    public function get($url, $params = array())
    {
        if (is_null($this->_ch)) {
            $this->init();
        }
        //如果是本地或开发环境，使用代理访问
//        if(in_array(env(), ['local', 'dev'])){
//            $this->setOption(CURLOPT_HTTPPROXYTUNNEL, true);
//            $this->setOption(CURLOPT_PROXYAUTH, CURLAUTH_BASIC);
//            $this->setOption(CURLOPT_PROXYTYPE, CURLPROXY_SOCKS5);
//            $this->setOption(CURLOPT_PROXY, '127.0.0.1');
//            $this->setOption(CURLOPT_PROXYPORT, '1080');
//        }

        $this->setOption(CURLOPT_RETURNTRANSFER, true);
        $this->setOption(CURLOPT_HTTPGET, true);
        $response = $this->exec($this->buildUrl($url, $params));
        $this->close();
        return $response;
    }

    public function post($url, $data = array())
    {
        if (is_null($this->_ch)) {
            $this->init();
        }
        $this->setOption(CURLOPT_POST, true);
        $this->setOption(CURLOPT_POSTFIELDS, $data);

        $response = $this->exec($url);
        $this->close();
        return $response;
    }

    public function postJson($url, $data = array())
    {
        if (is_null($this->_ch)) {
            $this->init();
        }
        $this->setOption(CURLOPT_POST, true);
        $this->setOption(CURLOPT_POSTFIELDS, http_build_query($data));

        $response = $this->exec($url);
        $this->close();
        return $response;
    }

    public function put($url, $data, $params = array())
    {
        // write to memory/temp
        $f = fopen('php://temp', 'rw+');
        fwrite($f, $data);
        rewind($f);

        if (is_null($this->_ch)) {
            $this->init();
        }
        $this->setOption(CURLOPT_PUT, true);
        $this->setOption(CURLOPT_INFILE, $f);
        $this->setOption(CURLOPT_INFILESIZE, strlen($data));

        $response = $this->exec($this->buildUrl($url, $params));
        $this->close();
        return $response;
    }

    public function delete($url, $params = array())
    {
        if (is_null($this->_ch)) {
            $this->init();
        }
        $this->setOption(CURLOPT_RETURNTRANSFER, true);
        $this->setOption(CURLOPT_CUSTOMREQUEST, 'DELETE');

        $response = $this->exec($this->buildUrl($url, $params));
        $this->close();
        return $response;
    }

    private function exec($url)
    {
        $this->setOption(CURLOPT_URL, $url);
        $this->response = curl_exec($this->_ch);
        if (!curl_errno($this->_ch)) {
            $header_size = curl_getinfo($this->_ch, CURLINFO_HEADER_SIZE);
            $body = substr($this->response, $header_size);
            return $body;
        } else {
            throw new \Exception(curl_error($this->_ch));
        }

    }

    public function buildUrl($url, $data = array())
    {
        $parsed = parse_url($url);
        isset($parsed['query']) ? parse_str($parsed['query'], $parsed['query']) : $parsed['query'] = array();
        $params = isset($parsed['query']) ? array_merge($parsed['query'], $data) : $data;
        $parsed['query'] = ($params) ? '?' . http_build_query($params) : '';
        if (!isset($parsed['path'])) {
            $parsed['path']='/';
        }

        $parsed['port'] = isset($parsed['port'])?':'.$parsed['port']:'';

        return $parsed['scheme'].'://'.$parsed['host'].$parsed['port'].$parsed['path'].$parsed['query'];
    }

    public function setOptions($options = array())
    {
        if (is_null($this->_ch)) {
            $this->init();
        }
        curl_setopt_array($this->_ch, $options);
        return $this;
    }

    public function setOption($option, $value)
    {
        if (is_null($this->_ch)) {
            $this->init();
        }
        curl_setopt($this->_ch, $option, $value);
        return $this;
    }

    public function setHeaders($header = array())
    {
        if ($this->isAssoc($header)) {
            $out = array();
            foreach ($header as $k => $v) {
                $out[] = $k .': '.$v;
            }
            $header = $out;
        }

        $this->setOption(CURLOPT_HTTPHEADER, $header);
        
        return $this;
    }

    private function isAssoc($arr)
    {
        return array_keys($arr) !== range(0, count($arr) - 1);
    }

    public function getError()
    {
        return curl_error($this->_ch);
    }

    public function getInfo()
    {
        return curl_getinfo($this->_ch);
    }

    public function close()
    {
        if (is_resource($this->_ch)) {
            curl_close($this->_ch);
        }
        $this->_ch = null;
    }


    public function getHeaders()
    {
        $headers = array();

        $header_text = substr($this->response, 0, strpos($this->response, "\r\n\r\n"));

        foreach (explode("\r\n", $header_text) as $i => $line) {
            if ($i === 0) {
                $headers['http_code'] = $line;
            } else {
                list ($key, $value) = explode(': ', $line);

                $headers[$key] = $value;
            }
        }

        return $headers;
    }

}