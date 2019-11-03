<?php 
namespace Concrete\Package\EsitefulPageAreaAlias\Block\PageAreaAlias;

use Concrete\Core\Block\BlockController;
use Loader;
use Page;
use Concrete\Core\Page\Type\Type as PageType;
use Concrete\Core\Block\BlockType\BlockTypeList;
use Symfony\Component\HttpFoundation\JsonResponse;

defined('C5_EXECUTE') or die("Access Denied.");

class Controller extends BlockController {
	
	protected $btTable = 'btPageAreaAlias';
	protected $btInterfaceWidth = "500";
	protected $btInterfaceHeight = "470";
	protected $btWrapperClass = 'ccm-ui';
	protected $btCacheBlockRecord = true;
	protected $btCacheBlockOutput = true;
	protected $btDefaultSet = '';

	public function getBlockTypeName() {
		return t("Page Area Alias");
	}
	
	public function getBlockTypeDescription() {
		return t("Block for displaying an area from another page.");
	}

	public function getSearchableContent(){
		// return $this->alertText;
	}

	public function validate($args) {
		$error = Loader::helper('validation/error');
		//if (trim($args['alertText']) == '') {
		//	$error->add(t('Alert Text Required'));
		//}		
		if($error->has()) {
			return $error;
		}
	}
		
	public function add() {
		$this->edit();
	}
	
	public function edit() {
		$blockTypes = BlockTypeList::getInstalledList();
		$this->set('blockTypes', $blockTypes);

		$pageTypes = PageType::getList();
		$pageTypeOpts = array();
		foreach($pageTypes as $pageType){
			$pageTypeOpts[$pageType->getPageTypeHandle()] = $pageType->getPageTypeName();
		}
		$this->set('pageTypes', $pageTypes);
		$this->set('pageTypeOpts', $pageTypeOpts);
	}

	public function view() {
		$this->set('aarHandle', $this->getAliasAreaHandle());
	}	
	
	public function save($data){		
		foreach($data as $key=>$value) {
			if($data === ''){
				$data[$key] = null;
			}
		}

		if($data['mode'] == 'inherit' || empty($data['mode'])){
			$data['acID'] = NULL;
			$data['actHandle'] = NULL;
			
		}else if($data['mode'] == 'page'){
			$data['actHandle'] = NULL;
		}else if($data['mode'] == 'page_type'){
			$data['acID'] = NULL;
		}
		
		if(empty($data['aarHandle'])){
			$data['aarHandle'] = NULL;	
		}
		
		if(empty($data['btRefHandles'])){
			$data['btRefHandles']=NULL;	
		}else if(is_array($data['btRefHandles'])){
			$data['btRefHandles'] = implode(',', $data['btRefHandles']);	
		}
		parent::save($data);
	}


	public function getMode(){
		if(empty($this->mode)){
			return 'inherit';
		}
		return $this->mode;
	}
	
	public function getBlockTypeRefMode(){
		if(empty($this->btRefMode)){
			return 'exclude';
		}
		return $this->btRefMode;
	}
	
	public function getBlockTypeRefHandles(){
		if(!empty($this->btRefHandles)){
			$ids = explode(',', $this->btRefHandles);
			return $ids;
		}
		return NULL;
	}
	
	public function getAliasCollectionID(){
		if($this->getMode()=='inherit'){
			$p = Page::getCurrentPage();
			$pcID = $p->getCollectionParentID();
			if(!empty($pcID)){
				return $pcID;
			}
		}else if($this->getMode() == 'page_type'){
			$ct = PageType::getByHandle($this->actHandle);
			return $ct->getMasterCollectionID();
		}else{
			return $this->acID;
		}
		return NULL;
	}
	
	public function getAliasCollection(){
		$acID = $this->getAliasCollectionID();
		if(!empty($acID)){
			return Page::getByID($acID);
		}
		return NULL;
	}
	
	public function getAliasAreaHandle(){
		if(empty($this->aarHandle)){
			//return handle of current area if none is set
			return $this->getBlockObject()->getAreaHandle();
		}
		return $this->aarHandle;
	}
	
	public function getAliasCollectionBlocks($areaHandle=NULL){
		
		$c = $this->getAliasCollection();
		if(is_null($areaHandle)){
			$areaHandle = $this->getAliasAreaHandle();	
		}
		
		if(is_object($c)){
			return $c->getBlocks($areaHandle);
		}
		return NULL;
	}
	
	public function filterBlocksByBlockType($types=NULL, $mode=NULL, $blocks=NULL){
		if(is_string($types)){
			$types = array($types);
		}else if(is_null($types)){
			$types = $this->getBlockTypeRefHandles();	
		}
		if(is_null($mode)){
			$mode = $this->getBlockTypeRefMode();	
		}
		if(is_null($blocks)){
			$blocks = $this->getAliasCollectionBlocks();	
		}
		$results = array();
		
		foreach($blocks as $block){
			$isFiltered = is_array($types) && in_array($block->getBlockTypeHandle(), $types);
			if(($mode == 'exclude' && !$isFiltered) || ($mode == 'include' && $isFiltered)){					
				$results[] = $block;
			}
		}
		return $results;
	}
	
	
	function resolveInheritedAliasBlocks($blocks=NULL){
		if(is_null($blocks)){
			$blocks = $this->getAliasCollectionBlocks();
		}
		//Look for alias blocks that we're inheriting and get the blocks from it
		foreach($blocks as $key=>$block){
			if($this->getBlockObject()->getBlockTypeHandle() == $block->getBlockTypeHandle()){
				$inheritAreaHandle = $block->getInstance()->aarHandle;
				if(empty($inheritAreaHandle)){
					//The auto area handles are not getting loaded into the block object properly from within the controller. view->blockObj->arHandle has it, so not sure what's up with that.	
					$block->getInstance()->aarHandle = $this->getAliasAreaHandle();
				}
				if(!in_array($block->bID, $this->displayAncestors)){
					$inheritBlocks = $block->getInstance()->getAliasBlocks(array_merge($this->displayAncestors, array($this->bID)));
				}
				//$this->pre($block->getBlockTypeHandle());
				//$this->pre($key);
				array_splice($blocks, $key, 1, $inheritBlocks);
				reset($blocks);
				
			}		
		}
		foreach($blocks as $block){
			//echo $block->getBlockTypeHandle().' :';	
		}
		return $blocks;
	}
	
	
	function getAliasBlocks($displayAncestors=array()){
		$this->displayAncestors = $displayAncestors;
		$blocks = $this->resolveInheritedAliasBlocks();
		$blocks = $this->filterBlocksByBlockType(NULL, NULL, $blocks);
		//$blocks = $this->filterBlocksByBlockType($this->$blocks); //filter it again

		return $blocks;
	}


	//
	// Additional Actions
	//

	public function page_area_handles(){
		$hlpJson = Loader::helper('json');
	
		$cID = isset($_REQUEST['cID']) ? $_REQUEST['cID'] : NULL;
		
		
		$json['error'] = false;
		$json['messages'] = array();
		
		if(empty($cID)){
			$json['error'] = true;
			$json['messages'][] = t('No collection ID was provided.');			
		}	
		//If there are errors, send them now
		if($json['error']){
			return new JsonResponse($json);
		}
		
		//Otherwise, provide the options
		$db = $this->app->make('database')->connection();
		$arHandles = $db->fetchColumn('select arHandle from Areas where cID = ? order by arHandle', [$cID]);
		
		$json['arHandles'] = $arHandles;

		return new JsonResponse($json);
	}
}
?>