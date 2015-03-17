/**
 * @license Copyright © 2013 Stuart Sillitoe <stuart@vericode.co.uk>
 * This work is mine, and yours. You can modify it as you wish.
 *
 * Stuart Sillitoe
 * stuartsillitoe.co.uk
 *
 */

CKEDITOR.plugins.add('strinsert',
{
	requires : ['richcombo'],
	init : function( editor )
	{
		//  array of strings to choose from that'll be inserted into the editor
		var strings = [];
		strings.push(['[[page_number]]', 'Page Number', 'Page Number']);
                strings.push(['[[tag_name]]', 'Tag Name', 'Tag Name']);
                strings.push(['[[image_qr_code]]', 'QR Code Image', 'QR Code Image']);
                strings.push(['[[image_bar_code]]', 'Bar Code Image', 'Bar Code Image']);
                strings.push(['[[unique_ode]]', 'Unique Code', 'Unique Code']);
                strings.push(['[[tag_description]]', 'Tag Description', 'Tag Description']);
                strings.push(['[[product_code]]', 'Product Code', 'Product Code']);
                strings.push(['[[last_process_status]]', 'Last Process Status', 'Last Process Status']);
                strings.push(['[[last_updated_by]]', 'Last Updated By', 'Last Updated By']);
                strings.push(['[[project_name]]', 'Project Name', 'Project Name']);
                strings.push(['[[project_address]]', 'Project Address', 'Project Address']);
                strings.push(['[[project_city]]', 'Project City', 'Project City']);
                strings.push(['[[project_country]]', 'Project Country', 'Project Country']);
                strings.push(['[[area]]', 'Project Area', 'Project Area']);
                strings.push(['[[project_location]]', 'Project Location', 'Project Location']);
                strings.push(['[[client_project_manager]]', 'client - project manager', 'client - project manager']);
                strings.push(['[[project_manager]]', 'Project management - Project Manager', 'Project management - Project Manager']);
                strings.push(['[[consultant]]', 'Consultant', 'Consultant']);
                strings.push(['[[project_director]]', 'Project Director', 'Project Director']);
                strings.push(['[[consultant_project_manager]]', 'Consultant Project Manager', 'Consultant Project Manager']);
                strings.push(['[[contractor_project_manager]]', 'Contractor-Project Manager', 'Contractor-Project Manager']);
                strings.push(['[[project_logo]]', 'Project Logo', 'Project Logo']);
                strings.push(['[[project_image]]', 'Project Image', 'Project Image']);
                strings.push(['[[company_name]]', 'Company Name', 'Company Name']);
                strings.push(['[[client_location]]', 'Client Location', 'Client Location']);
                strings.push(['[[main_contractor]]', 'Main Contractor', 'Main Contractor']);
                strings.push(['[[project_level]]', 'Project Level', 'Project Level']);
                strings.push(['[[items]]', 'Items', 'Items']);
                strings.push(['[[process]]', 'Process', 'Process']);

		// add the menu to the editor
		editor.ui.addRichCombo('strinsert',
		{
			label: 		'Placeholder',
			title: 		'Placeholder',
			voiceLabel: 'Placeholder',
			className: 	'cke_format placeholder',
			multiSelect:false,
			panel:
			{
				css: [ editor.config.contentsCss, CKEDITOR.skin.getPath('editor') ],
				voiceLabel: editor.lang.panelVoiceLabel
			},

			init: function()
			{
				this.startGroup( "Insert Placeholder" );
				for (var i in strings)
				{
					this.add(strings[i][0], strings[i][1], strings[i][2]);
				}
			},

			onClick: function( value )
			{
				editor.focus();
				editor.fire( 'saveSnapshot' );
				editor.insertHtml(value);
				editor.fire( 'saveSnapshot' );
			}
		});
	}
});