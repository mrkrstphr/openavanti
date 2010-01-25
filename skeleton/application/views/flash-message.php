
<?php if( isset( $_SESSION[ "flash" ] ) && !empty( $_SESSION[ "flash" ] ) ): ?>

	<div id="flash-message">
		<img alt="Message" src="/images/tick.png" title="<?php echo $_SESSION[ "flash" ]; ?>" />
		<?php echo $_SESSION[ "flash" ]; ?>
	</div>

<?php $_SESSION[ "flash" ] = ""; ?>

<?php endif; ?>
