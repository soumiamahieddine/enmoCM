<?php
/**
* Graphics Class
*
* Contains the functions to create graphics
*
* @package Maarch PeopleBox 1.0
* @version 1.0
* @since 06/2007
* @license GPL
* @author  Claire Figueras  <dev@maarch.org>
*
*/

/**
* Class graphics : Contains the functions to create graphics
*
* @author  Claire Figueras  <dev@maarch.org>
* @license GPL
* @package Maarch PeopleBox 1.0
* @version 1.0
*/
class graphics
{
	/**
	* Graphic border color
	*
    * @access private
    * @var Color Object
    */
	private $border_color;

	/**
	* Color of the text
	*
    * @access private
    * @var Color Object
    */
	private $typo_color;

	/**
	* General background of the graphic
	*
    * @access private
    * @var Color Object
    */
	private $background_color ;

	/**
	* Color of the plot
	*
    * @access private
    * @var Color Object
    */
	private $plot_color;

	/**
	* Color of the second plot
	*
    * @access private
    * @var Color Object
    */
	private $plot_color2;

	/**
	* Color of the first plot filling
	*
    * @access private
    * @var Color Object
    */
	private $filling_color;

	/**
	* Color of the second plot filling
	*
    * @access private
    * @var Color Object
    */
	private $filling_color2;

	/**
	* Grid color
	*
    * @access private
    * @var Color Object
    */
	private $grid_color;

	/**
	* Color of the axis
	*
    * @access private
    * @var Color Object
    */
	private $axis_color;

	/**
	* In the bar graphic, color of the shadow
	*
    * @access private
    * @var Color Object
    */
	private $bar_shadow_color;

	/**
	* Construct method : load the color
	*
	*/
	function __construct()
	{
		// $this->border_color = new Color(153, 153, 153);
		// $this->typo_color = new Color(0, 0, 0);
		// $this->plot_color = new Color(200, 0, 0, 20);
		// $this->filling_color2 = new Color(200, 80, 80, 75);
		// $this->filling_color = new Color(255, 250, 174, 50);
		// $this->grid_color = new Color(255, 255, 255);
		// $this->background_color = new Color(212, 208, 200);
		// $this->axis_color = new Color(102, 102, 102);
		// $this->bar_shadow_color = new Color(180, 180, 180, 10);
		// $this->plot_color2 = new Color(254, 194, 0);
	}


	public function show_stats_array($title, $data)
	{
		$nb_coll = count($data[0]);
		$keys = array_keys($data[0]);
		?><div align="center">
			<div><b><?php functions::xecho($title);?></b></div>
             <br/>
             <table  border="0" cellspacing="0" class="listing spec">
             	<thead>
				<tr>
                <?php for($i=0; $i< $nb_coll;$i++)
				{?>
                	<th><?php functions::xecho($data[0][$keys[$i]]);?></th>
                <?php
				}?>
                </tr>
                </thead>
               	<tbody>

                     <?php
					 $color = "";
					 for($i=1; $i< count($data);$i++)
					{
						if($color == ' class="col"')
						{
							$color = '';
						}
						else
						{
							$color = ' class="col"';
						}?>
                    	<tr <?php echo $color;?>>
                        	<?php
							for($j=0; $j< $nb_coll;$j++)
							{?>
								<td><?php functions::xecho($data[$i][$keys[$j]]);?></td>
								<?php
							}?>
                         </tr>
                    <?php
					}?>

                </tbody>
             </table>
        </div><?php
	}
}
?>
