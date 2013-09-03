<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

// CodeIgniter i18n library by Jérôme Jaglale
// http://maestric.com/en/doc/php/codeigniter_i18n
// version 6 - April 20, 2009

class MY_Language extends CI_Language {

	/**************************************************
	 configuration
	***************************************************/

	// languages
	var $languages = array(
		'en' => 'english',
		'fr' => 'french',
		'ta' => 'tamil'
	);

	// special URIs (not localized)
	var $special = array (
		""
	);
	
	// where to redirect if no language in URI
	var $default_uri = ''; 

	/**************************************************/
	
	
	function MY_Language()
	{
		parent::CI_Language();		
		
		global $CFG;
		global $URI;
		global $RTR;
		
		$segment = $URI->segment(1);
		
		if (isset($this->languages[$segment]))	// URI with language -> ok
		{
			$language = $this->languages[$segment];
			$CFG->set_item('language', $language);

			if ($RTR->scaffolding_request === TRUE)
			{
				// hide language segment for scaffolding code
				$RTR->uri->segments = $RTR->uri->rsegments;
			}
		}
		else if($this->is_special($segment)) // special URI -> no redirect
		{
			return;
		}
		else	// URI without language -> redirect to default_uri
		{
			// set default language
			$CFG->set_item('language', $this->languages[$this->default_lang()]);

			// redirect
			header("Location: " . $CFG->site_url($this->localized($this->default_uri)), TRUE, 302);
			exit;
		}
	}
	
	
	// get current language
	// ex: return 'en' if language in CI config is 'english' 
	function lang()
	{
		global $CFG;		
		$language = $CFG->item('language');
		
		$lang = array_search($language, $this->languages);
		if ($lang)
		{
			return $lang;
		}
		
		return NULL;	// this should not happen
	}
	
	
	function is_special($uri)
	{
		$exploded = explode('/', $uri);
		if (in_array($exploded[0], $this->special))
		{
			return TRUE;
		}
		if(isset($this->languages[$uri]))
		{
			return TRUE;
		}
		return FALSE;
	}
	
	
	function switch_uri($lang)
	{
		$CI =& get_instance();

		$uri = $CI->uri->uri_string();
		if ($uri != "")
		{
			$exploded = explode('/', $uri);
			if($exploded[1] == $this->lang())
			{
				$exploded[1] = $lang;
			}
			$uri = implode('/',$exploded);
		}
		return $uri;
	}
	
	// is there a language segment in this $uri?
	function has_language($uri)
	{
		$first_segment = NULL;
		
		$exploded = explode('/', $uri);
		if(isset($exploded[0]))
		{
			if($exploded[0] != '')
			{
				$first_segment = $exploded[0];
			}
			else if(isset($exploded[1]) && $exploded[1] != '')
			{
				$first_segment = $exploded[1];
			}
		}
		
		if($first_segment != NULL)
		{
			return isset($this->languages[$first_segment]);
		}
		
		return FALSE;
	}
	
	
	// default language: first element of $this->languages
	function default_lang()
	{
		foreach ($this->languages as $lang => $language)
		{
			return $lang;
		}
	}
	
	// add language segment to $uri (if appropriate)
	function localized($uri)
	{
		if($this->has_language($uri)
				|| $this->is_special($uri)
				|| preg_match('/(.+)\.[a-zA-Z0-9]{2,4}$/', $uri))
		{
			// we don't need a language segment because:
			// - there's already one
			// - or it's a special uri (set in $special)
			// - or that's a link to a file
		}
		else
		{
			$uri = $this->lang() . '/' . $uri;
		}
		
		return $uri;
	}
	
}
// END MY_Language Class

/* End of file MY_Language.php */
/* Location: ./system/application/libraries/MY_Language.php */
