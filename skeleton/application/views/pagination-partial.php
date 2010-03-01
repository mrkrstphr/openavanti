	<tr class="pagination">
		<td colspan="7">
			<?php
				if( !empty( $this->search->aPagination ) )
				{
					$sQueryString = StandardSearch::BuildQueryString();
				?>
				
				<table>
					<tr>
						<td class="results">
							Displaying Results <?php echo $this->search->aPagination[ "start" ]; ?> -
							<?php echo $this->search->aPagination[ "end" ]; ?> of
							<?php echo $this->search->aPagination[ "total" ]; ?>
						</td>
						
						<td class="links">
							<?php
								if( !empty( $this->search->aPagination[ "previous" ] ) )
								{
								?>
								
									<a onclick="<?php echo $this->search->sJavaScriptClick; ?>" href="<?php 
										echo $this->search->aPagination[ "previous" ][ "link" ]; ?>/<?php 
											echo $sQueryString; ?>">&lt; Previous</a> &nbsp;
								
								<?php
								}
								else
								{
								?>
								
									&lt; Previous &nbsp;
								
								<?php
								}
							?>
							
							<?php 
								foreach( $this->search->aPagination[ "links" ] as $aLink )
								{
									if( !empty( $aLink[ "link" ] ) )
									{
									?>
									
									<a onclick="<?php echo $this->search->sJavaScriptClick; ?>" href="<?php 
										echo $aLink[ "link" ]; ?>/<?php echo $sQueryString; ?>"><?php
											echo $aLink[ "page" ]; ?></a>
									
									<?php
									}
									else
									{
									?>
									
										<b><?php echo $aLink[ "page" ]; ?></b>
									
									<?php
									}
								}
							?>
							
							<?php
								if( !empty( $this->search->aPagination[ "next" ] ) )
								{
								?>
								
									&nbsp; <a onclick="<?php echo $this->search->sJavaScriptClick; ?>" href="<?php 
										echo $this->search->aPagination[ "next" ][ "link" ]; ?>/<?php 
											echo $sQueryString; ?>">Next &gt;</a>
								
								<?php
								}
								else
								{
								?>
								
									&nbsp; Next &gt;
								
								<?php
								}
							?>
						</td>
					</tr>
				</table>
				
				<?php
				}
				else
				{
					echo "&nbsp;";
				}
			?>
		</td>
	</tr>

