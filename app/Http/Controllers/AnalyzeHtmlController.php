<?php

namespace App\Http\Controllers;

use App\Http\Requests\HtmlFileRequest;
use App\Repositories\IAnalyzeHtmlRepository;
use App\Http\HttpResponses\HttpResponse;
class AnalyzeHtmlController extends Controller
{

    private $analyzeHtmlRepository;
    
    public function __construct(IAnalyzeHtmlRepository $iAnalyzeHtmlRepository) {
        $this->analyzeHtmlRepository = $iAnalyzeHtmlRepository;
    }

    public function analyze(HtmlFileRequest $request)
    {
        $file = $request->file('file');

        $response = $this->analyzeHtmlRepository->analyze($file);
        return HttpResponse::successResponse($response, 'Html has been analyzed successfully');
    }
}
