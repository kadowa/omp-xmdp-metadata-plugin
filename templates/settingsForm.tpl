{**
 * plugins/metadata/xmdp22/templates/settingsForm.tpl
 *
 * Copyright (c) 2015 Heidelberg University
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * DOI plugin settings
 *
 *}
<div id="description">{translate key="plugins.metadata.xmdp22.manager.settings.description"}</div>

<script type="text/javascript">
	$(function() {ldelim}
		// Attach the form handler.
		$('#xmdpSettingsForm').pkpHandler('$.pkp.controllers.form.AjaxFormHandler');
	{rdelim});
</script>
<form class="pkp_form" id="xmdpSettingsForm" method="post" action="{url router=$smarty.const.ROUTE_COMPONENT op="plugin" category="metadata" plugin=$pluginName verb="settings" save="true"}">
	{include file="common/formErrors.tpl"}
	{fbvFormArea id="ccFormArea" class="border" title="plugins.metadata.xmdp22.manager.settings.cc.settings"}
		{fbvFormSection}
			<p class="pkp_help">{translate key="plugins.metadata.xmdp22.manager.settings.cc.place"}</p>
			{fbvElement type="text" label="plugins.metadata.xmdp22.manager.settings.cc.place.required" required=true id="cc_place" value=$cc_place maxlength="50" size=$fbvStyles.size.MEDIUM}
		{/fbvFormSection}
		{fbvFormSection}
			<p class="pkp_help">{translate key="plugins.metadata.xmdp22.manager.settings.cc.address"}</p>
			{fbvElement type="textarea" label="plugins.metadata.xmdp22.manager.settings.cc.address.required" required=true id="cc_address" value=$cc_address maxlength="500" size=$fbvStyles.size.MEDIUM}
		{/fbvFormSection}
	{/fbvFormArea}
	{fbvFormArea id="ddbFormArea" class="border" title="plugins.metadata.xmdp22.manager.settings.ddb.settings"}
		{fbvFormSection}
			<p class="pkp_help">{translate key="plugins.metadata.xmdp22.manager.settings.ddb.contactID"}</p>
			{fbvElement type="text" label="plugins.metadata.xmdp22.manager.settings.ddb.contactID.addinfo" required=false id="ddb_contactID" value=$ddb_contactID maxlength="10" size=$fbvStyles.size.SMALL}
		{/fbvFormSection}
		{fbvFormSection}
			<p class="pkp_help">{translate key="plugins.metadata.xmdp22.manager.settings.ddb.kind"}</p>
			{fbvElement type="select" label="plugins.metadata.xmdp22.manager.settings.ddb.kind.required" name="ddb_kind" required=true id="ddb_kind" from=$ddbKindOptions selected=$ddb_kind translate=false size=$fbvStyles.size.MEDIUM}
		{/fbvFormSection}
	{/fbvFormArea}
	{fbvFormButtons submitText="common.save"}
</form>
<p><span class="formRequired">{translate key="common.requiredField"}</span></p>
