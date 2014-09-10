/* 
 * Insert the html for the download PDF montly statistics of Euro-Graph
 * @author Frederic Arijs
 */

CKEDITOR.plugins.add(
    'pdfstatbox',
    {
        init: function(editor) {
            editor.addCommand(
                'insertPdfstatbox',
                {
                    exec: function(editor) {
                        
                        var divhtml = '<div class="statBoxMonth clearer">'
                            + '<h5>Month goes here</h5>'
                            + '<p>A short description goes here</p>'
                            + '<a href="#" class="downloadPdfCTA">Download PDF</a></div>';
                        
                        editor.insertHtml(divhtml);
                    }
                }
            );
                
            editor.ui.addButton(
                'btn_pdfstatbox',
                {
                    label: 'Insert Montlhy PDF stat box',
                    command: 'insertPdfstatbox',
                    icon: this.path + 'images/pdfstatbox.png'
                }
            );
        }
    }
);

