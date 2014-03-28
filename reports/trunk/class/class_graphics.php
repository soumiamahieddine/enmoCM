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
require_once("apps".DIRECTORY_SEPARATOR.$_SESSION['config']['app_id'].DIRECTORY_SEPARATOR."tools".DIRECTORY_SEPARATOR."graphic_lib".DIRECTORY_SEPARATOR."LinePlot.class.php");
require_once("apps".DIRECTORY_SEPARATOR.$_SESSION['config']['app_id'].DIRECTORY_SEPARATOR."tools".DIRECTORY_SEPARATOR."graphic_lib".DIRECTORY_SEPARATOR."BarPlot.class.php");
require_once("apps".DIRECTORY_SEPARATOR.$_SESSION['config']['app_id'].DIRECTORY_SEPARATOR."tools".DIRECTORY_SEPARATOR."graphic_lib".DIRECTORY_SEPARATOR."Pie.class.php");
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
		$this->border_color = new Color(153, 153, 153);
		$this->typo_color = new Color(0, 0, 0);
		$this->plot_color = new Color(200, 0, 0, 20);
		$this->filling_color2 = new Color(200, 80, 80, 75);
		$this->filling_color = new Color(255, 250, 174, 50);
		$this->grid_color = new Color(255, 255, 255);
		$this->background_color = new Color(212, 208, 200);
		$this->axis_color = new Color(102, 102, 102);
		$this->bar_shadow_color = new Color(180, 180, 180, 10);
		$this->plot_color2 = new Color(254, 194, 0);
	}

/**
	* Constructs a graphic line
	*
	* @param integer  $width  general width of the graphic
	* @param integer  $height  general height of the graphic
	* @param array $values  array of the values
	* @param string  $title  graphic title
	* @param array  $labels  labels of the graphics
	* @param string  $Xlabel  label of the X axis
	* @param string  $Ylabel  label of the Y axis
	*/
	public function courbe($width = 400, $height = 400, $values, $title, $labels, $XLabel = "", $YLabel = "" )
	{

 		$graph = new Graph($width,$height);
		$graph->setAntiAliasing(TRUE);

		$graph->border->setColor($this->border_color);

  		 $plot = new LinePlot($values);
  		 $plot->title->set($title);
		 $plot->title->setColor($this->typo_color);
		 $plot->title->setFont(new TuffyBold(10));

	     $plot->setBackgroundColor($this->background_color);

		$plot->yAxis->title->set($YLabel);
		$plot->yAxis->title->setColor($this->typo_color);
		$plot->yAxis->title->setFont(new TuffyBold(10));
		$plot->yAxis->title->move(-10, 0);
		$plot->yAxis->setTitleAlignment(LABEL_MIDDLE);
		$plot->xAxis->label->setColor($this->typo_color);
		$plot->yAxis->label->setColor($this->typo_color);

		$plot->xAxis->title->set($XLabel);
		$plot->xAxis->title->setColor($this->typo_color);
		$plot->xAxis->title->setFont(new TuffyBold(10));
		$plot->xAxis->title->move(0, 6);
		$plot->xAxis->setTitleAlignment(LABEL_MIDDLE);

		//$plot->hideLine(TRUE);
		$plot->setColor($this->plot_color);
		$plot->setFillColor($this->filling_color);

		$plot->grid->setBackgroundColor($this->grid_color);

		//$plot->yAxis->setLabelPrecision(1);

	   // To add space around the plot
	   $plot->setSpace(
		  5, /* left */
		  5, /* right */
		  10, /* top */
		  NULL /* bottom */
	   );

      $plot->xAxis->setLabelText($labels);
	  $plot->yAxis->setColor($this->axis_color);
	  $plot->xAxis->setColor($this->axis_color);

   		 $graph->add($plot);

	 $graph->draw();
	}

	/**
	* Constructs a bars graphic
	*
	* @param integer  $width  general width of the graphic
	* @param integer  $height  general height of the graphic
	* @param array $values  array of the values
	* @param string  $title  graphic title
	* @param array  $labels  labels of the graphics
	* @param integer  $bottom_margin bottom margin
	* @param string  $Xlabel  label of the X axis
	* @param string  $Ylabel  label of the Y axis
	*/
	public function histo($width = 400, $height = 400, $values, $title, $labels, $bottom_margin, $XLabel ="", $YLabel = "")
	{

  		$graph = new Graph($width, $height);
   		$graph->setAntiAliasing(TRUE);
 		$graph->border->setColor($this->border_color);

	   $plot = new BarPlot($values);
		$plot->title->set($title);
		$plot->title->setFont(new TuffyBold(14));
		$plot->title->setColor($this->typo_color);
		$plot->grid->setBackgroundColor($this->grid_color);
		$plot->title->move(0, -15);
	   	$plot->setBarColor(
		 $this->plot_color
	   );
	   $plot->setSpace(0, 0, 5, NULL);
	   $plot->setPadding(NULL, NULL, 50, $bottom_margin);

	   $plot->setBackgroundColor($this->background_color);
		//$plot->setYMax(400);
	   $plot->barShadow->setSize(3);
	   $plot->barShadow->setPosition(Shadow::RIGHT_TOP);
	   $plot->barShadow->setColor($this->bar_shadow_color);
	   $plot->barShadow->smooth(TRUE);
	   $plot->xAxis->setLabelText($labels);

	   $plot->xAxis->label->setAngle(90);
		$plot->xAxis->label->setColor($this->typo_color);
		$plot->yAxis->label->setColor($this->typo_color);
	   $plot->xAxis->label->setFont(new Tuffy(10));

	   $plot->yAxis->title->set($YLabel);
		$plot->yAxis->title->setFont(new TuffyBold(10));
		$plot->yAxis->title->setColor($this->typo_color);
		$plot->yAxis->title->move(-10, 0);
		$plot->yAxis->setTitleAlignment(LABEL_MIDDLE);

		$plot->xAxis->title->set($XLabel);
		$plot->xAxis->title->setFont(new TuffyBold(10));
		$plot->xAxis->title->setColor($this->typo_color);
		$plot->xAxis->title->move(0, 65);
		$plot->xAxis->setTitleAlignment(LABEL_MIDDLE);
		$plot->yAxis->setColor($this->axis_color);
		$plot->xAxis->setColor($this->axis_color);
		$graph->add($plot);
		 $graph->draw();
	}

	/**
	* Constructs a graphic with 2 lines
	*
	* @param integer  $width  general width of the graphic
	* @param integer  $height  general height of the graphic
	* @param array $values  array of the values of the first line
	* @param string  $title  graphic title
	* @param array  $labels  labels of the graphics
	* @param string  $Xlabel  label of the X axis
	* @param string  $Ylabel  label of the Y axis
	* @param array $val2  array of the values of the second line
	* @param string  $plot1_legend  legend for the first line
	* @param string  $plot2_legend  legend for the second line
	*/
	public function groupe_courbes($width = 400, $height = 400, $values, $title, $labels, $XLabel = "", $YLabel = "", $val2, $plot1_legend, $plot2_legend )
	{

 		$graph = new Graph($width,$height);
		 $graph->setAntiAliasing(TRUE);

		$graph->border->setColor($this->border_color);

		$color_courbe2 = $this->plot_color;
		$color_courbe1 = $this->plot_color2;

		$group = new PlotGroup();

		  $group->setSpace(
		  5, //left
		  5, // right
		  10, // top
		  NULL //bottom
		 );

	   $group->setPadding(50, 20);
	   $group->setBackgroundColor($this->background_color);
	    $group->title->set($title);
	 	$group->title->setColor($this->typo_color);
		 $group->title->setFont(new TuffyBold(10));
	   $group->grid->setBackgroundColor($this->grid_color);

	   	$plot1 = new LinePlot($values);
		$plot1->setColor($color_courbe1);

		 $group->add($plot1);
		 $group->legend->add($plot1,$plot1_legend, LEGEND_LINE);
  			$group->legend->setTextColor($this->typo_color);


	   $group->axis->left->setLabelPrecision(1);

		$group->axis->left->title->set($YLabel);
		$group->axis->left->title->setColor($this->typo_color);
		$group->axis->left->title->setFont(new TuffyBold(10));
		$group->axis->left->title->move(-10, 0);

		$group->axis->bottom->title->set($XLabel);
		$group->axis->bottom->title->setColor($this->typo_color);
		$group->axis->bottom->title->setFont(new TuffyBold(10));
		$group->axis->bottom->title->move(0, 6);

		$group->axis->bottom->setLabelPrecision(1);
		$group->axis->bottom->setLabelText($labels);
		$group->axis->bottom->setColor($this->axis_color);
		$group->axis->left->setColor($this->axis_color);
		$group->axis->left->label->setColor($this->typo_color);
		$group->axis->bottom->label->setColor($this->typo_color);

		$plot1->setFillColor($this->filling_color);

		$plot2 = new LinePlot($val2);
		$plot2->setColor($color_courbe2);

		$group->add($plot2);
		 $group->legend->add($plot2, $plot2_legend, LEGEND_LINE);
		 $group->legend->setPosition(0.9,0.16);

  		  $graph->add($group);

		 $graph->draw();
	}

	/**
	* Constructs a pie graphic
	*
	* @param integer  $width  general width of the graphic
	* @param integer  $height  general height of the graphic
	* @param array $values  array of the values of the first line
	* @param string  $title  graphic title
	* @param array  $labels  labels of the graphics
	*/
public function camembert($width, $height, $values, $title, $labels)
{

	$graph = new Graph($width, $height);
	$graph->setAntiAliasing(TRUE);
	$graph->border->setColor($this->border_color);

	$graph->title->set($title);
	$graph->title->setFont(new TuffyBold(16));
	$graph->title->setColor($this->typo_color);

	$plot = new Pie($values, Pie::COLORED);


	$plot->setCenter(0.4, 0.55);

	$plot->setSize(0.5, 0.4);
	$plot->set3D(20);
	$plot->label->setColor($this->typo_color);

	$plot->setLegend($labels);


	$plot->legend->setPosition(1.65);
	$plot->legend->setBackgroundColor(new LightGray(60));
	$plot->legend->setTextColor($this->typo_color);

	$graph->add($plot);
	$graph->draw();
}

	public function show_stats_array($title, $data)
	{
		$nb_coll = count($data[0]);
		$keys = array_keys($data[0]);
		?><div align="center">
			<div><b><?php echo $title;?></b></div>
             <br/>
             <table  border="0" cellspacing="0" class="listing spec">
             	<thead>
				<tr>
                <?php for($i=0; $i< $nb_coll;$i++)
				{?>
                	<th><?php echo $data[0][$keys[$i]];?></th>
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
								<td><?php echo $data[$i][$keys[$j]];?></td>
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
