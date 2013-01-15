<?php
global $cowobo;

if (have_posts()) : while (have_posts()) : the_post();

$newtitle = $cowobo->L10n->the_title($post->ID);
$newcontent = $cowobo->L10n->the_content($post->ID);

//hide extra formating so its easier to edit
$stripped = str_replace(array('<br/>','</p>'), '\n', $newcontent);
$stripped = str_replace('<p>', '', $stripped);

echo '<form method="post" action="" enctype="multipart/form-data">';

	echo '<div class="tab">';
		echo '<h3>Translated Title</h3>';
		echo '<input type="text" tabindex="2" name="title-'.$lang.'" class="new" value="'.$newtitle.'"/>';
	echo '</div>';

	echo '<div class="tab">';
		echo '<h3>Original Title</h3>';
		echo '<div class="box notranslate">'.get_the_title().'</div>';
	echo '</div>';

	echo '<div class="tab">';
		echo '<h3>Translated Content</h3>';
		echo '<textarea tabindex="4" name="content-'.$lang.'" rows="12" class="new richtext">'.$stripped.'</textarea>';
	echo '</div>';

	echo '<div class="tab">';
		echo '<h3>Original Content</h3>';
		echo '<textarea tabindex="4" rows="12" class="new notranslate" disabled="disabled">'.get_the_content().'</textarea>';
	echo '</div>';

	echo '<div class="tab">';
		echo '<input type="hidden" name="correctlang" value="'.$lang.'"/>';
		echo '<button type="submit" class="button">Correct Translation</button>';
	echo '</div>';
echo '</form>';

endwhile; endif;
