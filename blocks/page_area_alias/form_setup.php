<?php  defined('C5_EXECUTE') or die("Access Denied."); ?> 
<?php


	$bt = $this->blockObj;

	$pageSelector = \Core::make('helper/form/page_selector');
	
	$cMode = $controller->getMode();
	
	
	//$btl = $a->getAddBlockTypes($c, $ap);
	//$blockTypes = $btl->getBlockTypeList();
	$hlpUrl = Loader::helper('concrete/urls');
	$form = Loader::helper('form');

	
	$db = Loader::db();
	$query = $db->query('select * from BlockTypes order by btName');
	
	
	//->getBlockAreaObject();
?>

<input type="hidden" name="arHandlesUrl" value="<?php echo $view->action('page_area_handles') ?>" />

<div class="form-group">
	<h2><?php echo t('Mode') ?></h2>
    <div>
        <label><?php echo $form->radio('mode', 'inherit', $cMode=='inherit') ?> <?php echo t('Parent Page') ?></label>
        <label><?php echo $form->radio('mode', 'page', $cMode=='page') ?> <?php echo t('Specific Page') ?></label>
        <label><?php echo $form->radio('mode', 'page_type', $cMode=='page_type') ?> <?php echo t('Page Type') ?></label>
    </div>
</div>

<div class="choose-collection">
	<h2><?php echo t('Choose a page') ?></h2>
    <div>
		<?php echo $pageSelector->selectPage('acID', $this->controller->getAliasCollectionID()) ?>
		<script>
			Concrete.event.bind('SitemapSelectPage', function(e, data) {
			    console.log(data);
			    ccm_PageAreaAliasForm.selectSitemapNode(data);
			});
		</script>
    </div>
</div>

<div class="choose-page-type">
	<h2><?php echo t('Choose a page type') ?></h2>
    <div>
		<?php echo $form->select('actHandle', $pageTypeOpts, $actHandle) ?>
        <?php foreach($pageTypes as $pageType){
			$ctHandle = $pageType->getPageTypeHandle();
			//$ctMasterID = $pageType->getMasterCollectionID();
			//echo "<input type='hidden' name='actmID_$ctHandle' value='$ctMasterID' />";
		}?>
      
    </div>
</div>

<div class="choose-area">
    <h2><?php echo t('Area to alias') ?></h2>
    <div>
	    <small><?php echo t('When set to "Auto", the block will use the same area name where it is placed.') ?></small><br/>
        <select name="aarHandle" id="aarHandle">
            <option value="">Auto</option>
        </select>        
    </div>
    
</div>

<div class="choose-block-types">
	<h2><?php echo t('Block types') ?></h2>
    <div>
        <p><?php echo t('%s the following block types:', $form->select('btRefMode', array('exclude'=>'Exclude', 'include'=>'Include'), $btRefMode)) ?></p>
        <ul class="btlist">
        <?php 
		$btRefHandlesArray = $this->controller->getBlockTypeRefHandles();
		
		foreach($blockTypes as $blockType){
			$btHandle = $blockType->getBlockTypeHandle();
			$btName = $blockType->getBlockTypeName();
			$btDesc = $blockType->getBlockTypeDescription();
			$checked = is_array($btRefHandlesArray) && in_array($btHandle, $btRefHandlesArray) ? 'checked' : '';
        	echo "<li><label title=\"$btDesc\"><input type=\"checkbox\" name=\"btRefHandles[]\" value=\"$btHandle\" $checked /> $btName</label></li>";
		} 
		?>
        </ul>
    </div>
</div>

<style type="text/css">
div.choose-mode,
div.choose-collection,
div.choose-page-type,
div.choose-area {margin-bottom:15px;}
</style>