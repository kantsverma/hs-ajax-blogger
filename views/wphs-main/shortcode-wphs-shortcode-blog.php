<ul id="blogResults" class="blogsection">
	<?php 
		// show blog page list here 
		if(!empty($blog_list)){
			echo $blog_list;
		}	
		// print the pagination here 
		if(!empty($blog_pagination)){
			echo $blog_pagination;
		}
	?>
</ul>
