<?php require 'header.php'; ?>

	<?php if(isset($content_template)) : ?>
		<?php require 'templates/' . $content_template . '.php'; ?>
	<?php else: ?>
		<?php require 'templates/default.php'; ?>
	<?php endif; ?>

<?php require 'footer.php'; ?>