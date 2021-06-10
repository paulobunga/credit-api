<?php

namespace App\Http\Response\Format;

use Dingo\Api\Http\Response\Format\Json as Base;

class Json extends Base
{
    /**
     * Encode the content to its JSON representation.
     *
     * @param mixed $content
     *
     * @return string
     */
    protected function encode($content)
    {
        $content['code'] = $content['code'] ?? $this->response->getStatusCode();
        $content['message'] = $content['message'] ?? $content['error'] ?? 'success';

        $jsonEncodeOptions = [];
        // Here is a place, where any available JSON encoding options, that
        // deal with users' requirements to JSON response formatting and
        // structure, can be conveniently applied to tweak the output.

        if ($this->isJsonPrettyPrintEnabled()) {
            $jsonEncodeOptions[] = JSON_PRETTY_PRINT;
            $jsonEncodeOptions[] = JSON_UNESCAPED_UNICODE;
        }

        $encodedString = $this->performJsonEncoding($content, $jsonEncodeOptions);

        if ($this->isCustomIndentStyleRequired()) {
            $encodedString = $this->indentPrettyPrintedJson(
                $encodedString,
                $this->options['indent_style']
            );
        }
        return $encodedString;
    }
}
