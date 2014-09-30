<?php

class Sydney_View_Helper_Config
{

    /**
     *
     * @return Sydney_View_Helper_ContentTypeCollection
     */
    public static function getDefault(){

        $contentTypeCollection = new Sydney_View_Helper_ContentTypeCollection();
        $contentTypeCollection->add('heading-block', new Sydney_View_Helper_ContentType('Heading', 'ContentHeading', 'ContentHeading', 'EditorHeading'));
        $contentTypeCollection->add('text-block', new Sydney_View_Helper_ContentType('Text', 'ContentText', 'ContentText', 'EditorText'));
        $contentTypeCollection->add('file-block', new Sydney_View_Helper_ContentType('Files list', 'ContentFile', 'ContentFile', 'EditorFiles'));
        $contentTypeCollection->add('view-embedder-block', new Sydney_View_Helper_ContentType('View embedder', 'ContentViewembeder', 'ContentViewembeder', 'EditorViewembeder'));
        $contentTypeCollection->add('plain-text-html-block', new Sydney_View_Helper_ContentType('Plain text/HTML', 'ContentFreeText', 'ContentFreeText', 'EditorFreeText'));

        return $contentTypeCollection;
    }
}