
<?php if( Validation::HasErrors() ): ?>

	<?php $aErrors = Validation::GetErrors(); ?>

	<div class="validation-errors">
		<img alt="Error" src="/images/error.png" title="Validation Errors" />
		The following validation errors have occurred:
		
		<ol>
			<?php foreach( $aErrors as $sField => $aFieldErrors ): ?>
			<?php foreach( $aFieldErrors as $sError ): ?>
			
			<li>
				<?php echo $sError; ?>
			</li>
			
			<?php endforeach; ?>
			<?php endforeach; ?>
		</ol>
	</div>

<?php endif; ?>
