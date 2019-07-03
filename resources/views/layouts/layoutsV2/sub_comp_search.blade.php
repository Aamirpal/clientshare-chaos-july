<ul id="sub-comp-list">
<?php foreach($sub_comp_details as $comp) { if(isset($comp['sub_comp']["company_name"])) { ?>
	<li onClick="selectComp('<?php echo $comp['sub_comp']["company_name"]; ?>');"><?php echo $comp['sub_comp']["company_name"]; ?></li>
	<input type="hidden" class="sub-company-hidden-input" value="<?php echo $comp['sub_comp']["company_name"]; ?>">
	<?php } }?>
<li class="sub_comp_add_list"></li>
</ul>
