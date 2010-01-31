
<?php if(OpenAvanti\Validation::hasErrors()): ?>

	<?php $errors = OpenAvanti\Validation::getErrors(); ?>

	<div class="validation-errors">
		<img alt="Error" src="/images/icons/silk/error.png" title="Validation Errors" />
		The following validation errors have occurred:
		
		<ol>
			<?php foreach($errors as $fieldErrors): ?>
			<?php foreach($fieldErrors as $error): ?>
			
			<li><?php echo $error; ?></li>
			
			<?php endforeach; ?>
			<?php endforeach; ?>
		</ol>
	</div>

<?php endif; ?>
