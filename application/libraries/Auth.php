<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');


class Auth
{
    protected $CI;

    public function __construct()
    {
        $this->CI =& get_instance();
        
        
    }
    
	public function checkKey()
	{
       $key = $this->CI->input->get('key');

       if($key != md5('SecureAuth@2018!')){
        
        redirect('pancom/index');
       }
	}

    

}