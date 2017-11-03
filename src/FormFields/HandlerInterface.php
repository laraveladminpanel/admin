<?php

namespace TCG\Voyager\FormFields;

use Illuminate\Http\Request;

interface HandlerInterface
{
    public function handle($row, $dataType, $dataTypeContent);

    public function createContent($row, $dataType, $dataTypeContent, $options);

    public function supports($driver);

    public function getCodename();

    public function getName();

    public function getContentBasedOnType(Request $request, $slug, $row);
}
