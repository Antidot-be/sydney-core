/**
 *
 * @author Arnaud Selvais
 */
if (!ANTIDOT) var ANTIDOT = {};
/**
 * Class: ANTIDOT.NestedArray
 * Class to get the structure or a UL in a
 * nested array so we can save and define the order
 * in a proper way
 * @param elId - {Object} elId ID of the element to treat
 * @constructor
 */
ANTIDOT.NestedArray = function( elId )
{
    this.rootEl = document.getElementById( elId );
    this.aStructure = [];

    /**
     * Method: getLiArray
     * @param liItems - {Object} liItems
     */
    this.getLiArray = function( liItems )
    {
        var toretAr=[];
        for(var i=0; i < liItems.length; i++)
        {
            var tElem = liItems[i];
            if (tElem.nodeName == 'LI') {
                if (tElem.attributes) {
                    if (tElem.attributes['dbid'])
                        var dbid = tElem.attributes['dbid'].nodeValue;
                    else
                        var dbid = 0;
                    if (tElem.attributes['dborder'])
                        var dborder = tElem.attributes['dborder'].nodeValue;
                    else
                        var dborder = 0;
                }
                else {
                    var dbid = 0;
                    var dborder = 0;
                }
                var kidsv = [];
                kidsv = this.getLiArray( tElem.nextS );

                toretAr.push({
                    eltype: tElem.nodeName,
                    elid: tElem.id,
                    dbid: dbid,
                    dborder: dborder,
                    kids: kidsv
                });
            }
        }
        return toretAr;
    };
    /**
     * Method: buildStructure
     */
    this.buildStructure = function() {
        var dborder 	= 0;
        var aStructure 	= [];

        $("#viewcontent").dynatree("getRoot").visit(function(node){
            aStructure.push({
                eltype: node.data.title,
                elid: node.data.key,
                dbid: node.data.key,
                dborder: dborder,
                ndborder: dborder,
                parentid: node.parent.data.key
            });
            dborder++;
        });

        this.aStructure = aStructure;

    };

    /**
     * Initialization of the object (launched automatically)
     */
    this.init = function()
    {
        this.buildStructure();
    };
    // launcher
    this.init();
};


