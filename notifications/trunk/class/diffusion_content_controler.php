<?php

/*
*   Copyright 2008-2011 Maarch
*
*   This file is part of Maarch Framework.
*
*   Maarch Framework is free software: you can redistribute it and/or modify
*   it under the terms of the GNU General Public License as published by
*   the Free Software Foundation, either version 3 of the License, or
*   (at your option) any later version.
*
*   Maarch Framework is distributed in the hope that it will be useful,
*   but WITHOUT ANY WARRANTY; without even the implied warranty of
*   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*   GNU General Public License for more details.
*
*   You should have received a copy of the GNU General Public License
*   along with Maarch Framework. If not, see <http://www.gnu.org/licenses/>.
*/

/**
* @brief Contains the diffusion_type Object
* (herits of the BaseObject class)
*
* @file
* @author Loïc Vinet - Maarch
* @date $date$
* @version $Revision$
* @ingroup core
*/

//Loads the required class
try {
	require_once 'modules/notifications/class/diffusion_content.php';
	require_once 'core/class/ObjectControlerAbstract.php';
} catch (Exception $e) {
    echo $e->getMessage() . ' // ';
}

/**
 * Class for controling docservers objects from database
 */
class diffusion_content_controler
    extends ObjectControler 
    //implements ObjectControlerIF
{
    /**
     * Get event with given event_id.
     * Can return null if no corresponding object.
     * @param $id Id of event to get
     * @return event
     */
    public function getAllContents()
    {
		core_tools::load_lang();
		$return = array();
		$xmlfile = 'modules/notifications/xml/diffusion_content.xml';
        
        $xmlcontent = simplexml_load_file($xmlfile);
        foreach($xmlcontent->diffusion->content as $content) {
			//<id> <label> <script>	
			
			$diffusion_content = new diffusion_content();
			
			$diffusion_content -> id = utf8_decode((string) $content->id);
			if(@constant(utf8_decode((string) $content->label))) {
				$diffusion_content -> label = constant(utf8_decode((string) $content->label));
			} else {
				$diffusion_content -> label = utf8_decode((string) $content->label);
			}
			
			$diffusion_content -> script = utf8_decode((string) $content->script);
		
			$return[$diffusion_content->id] = $diffusion_content;
		}
		
        if (isset($return)) {
            return $return;
        } else {
            return null;
        }
    }
  
	public function getDiffusionContent($content_id)
	{
		if ($content_id <> '')
		{
			$fulllist = array();
			$fulllist = $this->getAllContents();
			
			foreach ($fulllist as $dc_id => $dc)
			{
				if ($content_id == $dc_id){
					return $dc;
				}
			}
		}
		return null;
	}
   
  

}

