<?php
require_once('Zend/View/Helper/Abstract.php');

/**
 * Helper showing the structure of a site in a drop down box
 *
 * @package SydneyLibrary
 * @subpackage ViewHelper
 * @author Arnaud Selvais
 * @since 08/06/09
 * @todo Implement the translation method here
 */
class Sydney_View_Helper_DataTable extends Zend_View_Helper_Abstract
{
    public $t = array();

    /**
     *
     * @param string $l
     */
    private function _tra($l)
    {
        if (count($this->t) == 0 || !isset($this->t[$l])) {
            return addslashes(ucfirst(preg_replace('/_/', ' ', $l)));
        } else {
            return addslashes($this->t[$l]);
        }
    }

    /**
     *
     * Returns all you need to show data in a datatable widget from YUI
     * @param string $model Name of the model to use for the data
     * @param Array $oCustom Custom configuration options
     */
    public function DataTable($model, $oCustom = array())
    {
        //$mDB = new $model;
        $moduleName = $this->view->moduleName;
        $crtlName = $this->view->controllerName;
        // default params
        $oDefaults = array(
            // max number of rows per page. Default is 50
            'rowperpages'    => 50,
            // Sort by... Default is id
            'sortby'         => 'id',
            // Sort direction... Default is ASC
            'sortdir'        => 'ASC',
            // list of fields to be hidden separated by a comma (if we use a common model mapped from a table, we can hide some colomns). Default is null
            'hidefields'     => null,
            // Should we show the edit buttons. Default is true
            'showedit'       => true,
            // 	Should we show the edit buttons. Default is true
            'showdelete'     => true,
            // URL for edition. Default is '/[moduleName]/[controllerName]/edit[model]/' with the ID added as param, ex:  /adminglobal/services/editcompanies/id/2
            'editurl'        => "/" . $moduleName . "/" . $crtlName . "/edit" . strtolower($model) . "/",
            // URL for deletion. Default is '/[moduleName]/services/delete[model]/format/json/' with the ID added as param, ex:  /adminglobal/services/deletecompanies/id/2
            'deleteurl'      => "/" . $moduleName . "/services/delete" . strtolower($model) . "/format/json/",
            // URL for the data update when inline editing a cell
            'updateurl'      => "/" . $moduleName . "/services/update" . strtolower($model) . "/format/json/",
            // url of the service (where to get the data). Default is null, so it will be built for example: '/adminglobal/services/getcompanies/format/json/hidefields/id,name,valid,code'
            'srvurl'         => null,
            // params we want to add to the URL. Default is null (meaning none)
            'paramsurl'      => null,
            // Labels for the columns. Key = Label. By default the labels will be the field name with the first letter in caps and the underscores will be replaced by spaces.
            'labels'         => array(),
            // rows which are editable on the fly
            'editablecols'   => '',
            // can the rows be selected?
            'selectabletows' => true,
            // if this is null, the height will be automatic according to the page height, otherwise it will be fixed
            'height'         => null,
            // should the edition show up in a jquery dialog box?
            'editindialog'   => false,
            // are the delete operation executed async (json call)
            'deletejson'     => true,
            // filters
            'filters'        => ''
        );
        $o = array_merge($oDefaults, $oCustom);

        $this->t = $o['labels'];
        $html = '';
        $d = new $model;
        $fieldsNames = $d->fieldsNames;
        $hidefields = array();
        $editablecolsA = explode(',', $o['editablecols']);
        if ($o['hidefields'] != null) {
            $hidefields = explode(',', $o['hidefields']);
        }
        if (is_array($hidefields) && count($hidefields) > 0) {
            $fieldsNames = array();
            foreach ($d->fieldsNames as $fn) {
                if (!in_array($fn, $hidefields)) {
                    $fieldsNames[] = $fn;
                }
            }
        }
        // builds the service URL
        if ($o['srvurl'] == null) {
            $srvurl = '/' . $moduleName . '/services/get' . strtolower($model) . '/format/json/hidefields/' . implode(',', $fieldsNames);
        } else {
            $srvurl = $o['srvurl'];
        }

        // add params to URL if any
        $paramsurl = array();
        if ($o['paramsurl'] != null) {
            parse_str($o['paramsurl'], $paramsurl);
            $srvurl .= $o['paramsurl'];
        }

        // build the HTML
        $html .= '
<script type="text/javascript">
var ' . $model . 'DataTable = {};
YAHOO.util.Event.addListener(window, "load", function(){ ' . $model . 'DataTable = ' . $model . 'DataTableClass(); });
' . $model . 'DataTableClass = function() {
        var rowPerPages = ' . $o['rowperpages'] . ';
        ';

        if ($o['showedit'] || $o['showdelete']) {

            if ($o['showedit']) {
                $html .= 'var editUrl = "' . $o['editurl'] . '";';
            }

            if ($o['showdelete']) {
                $html .= 'var deleteUrl = "' . $o['deleteurl'] . '";';
            }

            $html .= 'YAHOO.widget.DataTable.Formatter.myButtonFormatter = function(elLiner, oRecord, oColumn, oData) {';
            $html .= 'var mHtml = ""; ';

            if ($o['showedit']) {
                if ($o['editindialog']) {
                    $html .= ' if (editUrl != "") mHtml += "<a href=\"javascript:editClickedDia(\'"+editUrl+"id/"+oRecord.getData("id")+"?forModule=' . $paramsurl['forModule'] . '\');\" class=\"button tablebuttonedit\">Edit</a>";';
                } else {
                    $html .= ' if (editUrl != "") mHtml += "<a href=\""+editUrl+"id/"+oRecord.getData("id")+"?forModule=' . $paramsurl['forModule'] . '\" class=\"button tablebuttonedit\">Edit</a>"; ';
                }
            }

            if ($o['showdelete']) {
                if ($o['deletejson']) {
                    $html .= '	if (deleteUrl != "") mHtml += "<a href=\"javascript:delClickedJson(\'"+deleteUrl+"id/"+oRecord.getData("id")+"\');\" class=\"button warning deletenodea tablebuttondelete\">Delete</a>"; ';
                } else {
                    $html .= '	if (deleteUrl != "") mHtml += "<a href=\""+deleteUrl+"id/"+oRecord.getData("id")+"\" class=\"button warning deletenodea tablebuttondelete\">Delete</a>"; ';
                }
            }

            $html .= ' elLiner.innerHTML = mHtml;';
            $html .= '};';
        }

        if (count($fieldsNames) > 0) {
            $html .= "
    var submitCellContent = function(callback, newValue) {
        var record = this.getRecord();
        var column = this.getColumn();
        var oldValue = this.value;
        var datatable = this.getDataTable();
        if (newValue != oldValue) {
        YAHOO.util.Connect.asyncRequest('POST', '" . $o['updateurl'] . "',
        {
            success: function (o) {
                var r = YAHOO.lang.JSON.parse(o.responseText);
				if (r.result.status == 'ok') callback(true, r.result.newValue);
				else callback();
            },
            failure: function (o) {
                alert('Failure...');
                callback();
            },
            scope: this
        },
        'action=cellEdit&record=' + YAHOO.lang.JSON.stringify(record) + '&column=' + column.key + '&newValue=' + escape(newValue) + '&oldValue=' + escape(oldValue) + '&id=' + record.getData('id')
        );
        } else callback(true, oldValue);
    };
	";
        }
        if ($o['editindialog']) {
            $html .= '
		var filedialog;
		var createFialog = function() {
			filedialog = $(".filepropdialog").dialog({
				"title":"File properties",
				"width":600,
				"height": 500,
				"closeOnEscape": false,
				autoOpen: false,
				close: function(){ filedialog.html("..."); }
			});
			filedialog.html("Loading...");
		};
		createFialog();
		editClickedDia = function( url ) {
			filedialog.load( url );
			filedialog.dialog("open");
		};
		';
        }
        if ($o['deletejson']) {
            $html .= '
		delClickedJson = function( url ) {
			if (confirm("Are you sure you want to do that?")) {
			$.getJSON(url,function(data){
				$(\'#ajaxbox\').msgbox( data );
				console.log(data.rowsDeleted);

				if (data.status == 1) {
					var rd = data.rowsDeleted.split(",");
					var m = ' . $model . 'DataTable.oDT.getIdsAndRowsIds();
					for (var i=0; i < rd.length; i++) {
						' . $model . 'DataTable.oDT.deleteRow( m[(rd[i])] );
					}
				}
			});
			};
		};
		';
        }

        $html .= '
		var myColumnDefs = [ ';
        for ($i = 0; $i < count($fieldsNames); $i++) {
            if (in_array($fieldsNames[$i], $editablecolsA)) {
                $editorr = ",editor: new YAHOO.widget.TextboxCellEditor({disableBtns:true, asyncSubmitter:submitCellContent})";
            } else {
                $editorr = '';
            }

            $html .= "{key:'" . $fieldsNames[$i] . "', label:'" . $this->_tra($fieldsNames[$i]) . "',sortable:true " . $editorr . " ,maxAutoWidth:$('.subBox').width()/" . ((count($fieldsNames)) * 1.5) . "}";

            if ($i < (count($fieldsNames) - 1)) {
                $html .= ", ";
            }
        }

        if ($o['showedit'] || $o['showdelete']) {
            $html .= ",{key:'ebuttons','label':'Actions',sortable:false,formatter:YAHOO.widget.DataTable.Formatter.myButtonFormatter}";
        }

        $html .= '
			];
        var myDataSource = new YAHOO.util.DataSource("' . $srvurl . '?");
        myDataSource.responseType = YAHOO.util.DataSource.TYPE_JSON;
        myDataSource.responseSchema = {
            resultsList: "ResultSet.Result",
            fields: [ ';
        $attrr = "'}";
        $html .= "{key:'";
        $html .= implode($attrr . ",
		{key:'", $fieldsNames);
        $html .= $attrr;
        $html .= '
                    ],
			metaFields: {
				totalRecords: "ResultSet.totalRecords"
			}
        };
        var maxheight = $(document).height();
        zehe = (maxheight-480);
        if (zehe < 200) zehe = 200;
        ';

        if ($o['height'] != null && $o['height'] > 0) {
            $html .= ' zehe = ' . $o['height'] . '; ';
        }

        $html .= '
        var myConfigs = {
                initialRequest: "filters=' . urlencode($o['filters']) . '&sort=' . $o['sortby'] . '&dir=' . $o['sortdir'] . '&startIndex=0&results=' . $o['rowperpages'] . '",
                dynamicData: true,
                sortedBy : {key:"' . $o['sortby'] . '", dir:"' . $o['sortdir'] . '"},
                paginator: new YAHOO.widget.Paginator({ rowsPerPage:' . $o['rowperpages'] . ' }),
                height: zehe+"px",
                selectionMode:"multiple"
		};
        var myDataTable = new YAHOO.SydneyGrid("' . $model . 'Data", myColumnDefs, myDataSource, myConfigs );
        myDataTable.getSelectedIds = function () {
	        var a = ' . $model . 'DataTable.oDT.getSelectedRows();
	        var b = [];
			for (var i=0; i < a.length; i++) {
			    b.push( parseInt( $("#"+a[i]+" > td > div").html() ) );
			}
			return b;
		}
        myDataTable.getIdsAndRowsIds = function () {
			var tor={};
			$("#' . $model . 'Data table tr").each(function(){
			    var roid = $(this).attr("id");
			    var id = $("td:first > div", $(this)).html();
			    tor[id] = roid;
			});
			return tor;
       		}

        ';

        // makes the rows selectable
        if ($o['selectabletows']) {
            $html .= '
	        myDataTable.subscribe("rowMouseoverEvent", myDataTable.onEventHighlightRow);
	        myDataTable.subscribe("rowMouseoutEvent", myDataTable.onEventUnhighlightRow);
	        myDataTable.subscribe("rowClickEvent", myDataTable.onEventSelectRow);
	        ';
        }
        // makes fields editable
        if (count($editablecolsA) > 0) {
            $html .= ' myDataTable.subscribe("cellClickEvent", myDataTable.onEventShowCellEditor); ';
        }
        $html .= '
			myDataTable.handleDataReturnPayload = function(oRequest, oResponse, oPayload) {
				oPayload.totalRecords = oResponse.meta.totalRecords;
				if ( oPayload.totalRecords <= rowPerPages ) myDataTable._configs.dynamicData = false;
				return oPayload;
			};
		';
        /*****
         * $html .=  '
         * var mySuccessHandler = function() {
         * this.set("sortedBy", null);
         * this.onDataReturnAppendRows.apply(this,arguments);
         * };
         * var myFailureHandler = function() {
         * this.showTableMessage(YAHOO.widget.DataTable.MSG_ERROR, YAHOO.widget.DataTable.CLASS_ERROR);
         * this.onDataReturnAppendRows.apply(this,arguments);
         * };
         * var callbackObj = {
         * success : mySuccessHandler,
         * failure : myFailureHandler,
         * scope : myDataTable
         * };
         * ';
         *****/
        $html .= '
			        return {
			            oDS: myDataSource,
			            oDT: myDataTable
			        };
			};

			</script>
			<div class="yui-skin-sam"><div id="' . $model . 'Data" style="max-width:100%;"></div></div>
			';

        return $html;
    }
}
