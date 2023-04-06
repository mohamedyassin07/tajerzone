<?php
    
    class optional
    {
        private $var;
        private $default;
        
        public function __construct($var , $default = '')
        {
            $this->var = $var;
            $this->default = $default;
        }
        
        public function __get($name)
        {
            if(isset($this->var[$name]))
            {
                return $this->var[$name];
            }
            else
            {
                return $this->default;
            }
        }
    
    }
    
    function optional($var , $default = '')
    {
        return new optional($var , $default);
    }