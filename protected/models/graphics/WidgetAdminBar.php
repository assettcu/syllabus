<?php


class WidgetAdminBar 
{
	
	public function __construct() 
	{
		if(!defined("WIDGET_ADMINBAR")) {
			$cs = Yii::app()->getClientScript();
			$cs->registerScriptFile(HTTPS."://".LIBRARY_DIRECTORY."/javascript/jquery/modules/tiptip/jquery.tipTip.minified.js");
			$cs->registerCssFile(HTTPS."://".LIBRARY_DIRECTORY."/javascript/jquery/modules/tiptip/tipTip.css");
			define("WIDGET_ADMINBAR",true);
		}
	}
	
	public function render() 
	{
		$output = "<div class=\"admin-bar\">";
		if(count($this->buttons)>0) {
			ob_start();
			foreach($this->buttons as $button) {
				?>
			    <div class="admin-button ui-widget-header <?php if($button->active): ?>active<?php endif; ?> <?=@$button->action_name?>" title="<?=@$button->title;?>">
			        <div class="icon"><?=StdLib::load_image(@$button->image.".png","20px");?></div>
			        <div class="button-text" <?php if(@$button->text == ""): ?>style="display:none;"<?php endif; ?>> <?=@$button->text?></div>
			    </div>
			    <?php
			}
			$output .= ob_get_contents();
		}
		$output .= "</div>";
		ob_end_clean();
		
		$this->render_style();
		$this->render_js();
		echo $output;
	}
	
	public function render_js()
	{
		?>
		<script type="text/javascript">
			jQuery(document).ready(function($){
				$("div.admin-button").tipTip();
				$("div.admin-button").hover(
					function() {
						$(this).fadeTo("fast",1);
					},
					function() {
						$(this).fadeTo("fast",0.5);
					}
				);
			});
		</script>
		<?php
	}
	
	public function render_style()
	{
		if(!defined("ADMINBAR_STYLE")) {
			?>
			<style>
			img.slideshow-img {
				display:none;
			}
			div.admin-bar {
			    float:right;
			    padding:0;
			    margin:0;
			    margin-bottom:10px;
			}
			div.admin-button {
			    border:1px solid #69f;
			    padding:3px;
			    border-radius:5px;
			    display:inline-block;
			    opacity:0.5;
			}
			.active {
			    cursor:pointer;
			}
			.disabled {
			    cursor:default;
			}
			.selected {
			    cursor:pointer;
			}
			div.button-text {
				float:right;
				padding-left:5px;
				padding-top:2px;
				padding-right:5px;
			}
			div.icon {
				float:left;
			}
			</style>
			<?php
			define("ADMINBAR_STYLE",true);
		}
	}
	
	public function create_button($options)
	{
		$button = new stdClass;
		foreach($options as $key => $value) {
			$button->$key = $value;
		}
		$this->buttons[] = $button;
	}
	
}
