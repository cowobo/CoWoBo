<?php

echo '<div class="tab">';

echo '<ul class="notranslate horlist">';
	//convert this to html
	foreach($langnames as $key => $langname): $x++;
		$javaoff = '?q='.$key;
		$javaon = '?lang='.$key.'#googtrans(en|'.$key.')';
		$shortkey = explode('-', $key);
		if($shortkey[0] == 'en'):
			$javaoff = '?lang='.$key;		
			$javaon = '?lang='.$key;
		endif;?>
		<li><a href="<?php echo $javaoff;?>" onclick="window.location='<?php echo $javaon;?>'; return false;"><?php echo $langname[0];?></a></li><?php
	endforeach;	
echo '</ul>';

echo '</div>';