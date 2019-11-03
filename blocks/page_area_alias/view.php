<?php
	$isEditMode = Page::getCurrentPage()->isEditMode();

	$aliasCollection = $controller->getAliasCollection();
	
	if(!is_object($aliasCollection)){
		if($isEditMode)
			echo '<div style="min-height:150px;min-width:50px;width:100%;height:100%;">'.t('The referenced page could not be found (Page Area Alias Block).').'</div>';
		
		return;	
	}else{
		$aliasArea = Area::get($aliasCollection, $aarHandle);	
	}
	
	if($isEditMode && !is_object($aliasArea)){		
		echo '<div style="min-height:150px;min-width:50px;width:100%;height:100%;">The "'.$aarHandle.'" area does not exist on the referenced page (Page Area Alias Block).</div>';
		return;
	}
	
	$aliasBlocks = $controller->getAliasBlocks();
	
	foreach($aliasBlocks as $aliasBlock){
		$p = new Permissions($aliasBlock);
		if($p->canRead()){
			if(strlen($view = $aliasBlock->getBlockfilename()) > 0){
				$aliasBlock->display($view);
			}else{
				$aliasBlock->display();	
			}
		}
	}
	
	if($isEditMode && count($aliasBlocks) < 1){
		echo '<div style="min-height:150px;min-width:50px;width:100%;height:100%;">(Page Area Alias Block)</div>';
		return;	
	}
	
	
	
	
?>
