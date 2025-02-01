<?php

namespace App\Services;

use Symfony\Component\DomCrawler\Crawler;
use App\Repositories\IAnalyzeHtmlRepository;
use Illuminate\Http\Exceptions\HttpResponseException;
use App\Http\HttpResponses\HttpResponse;
use Illuminate\Support\Facades\Log;

class AnalyzeHtmlService implements IAnalyzeHtmlRepository
{
    /**
     * Create a new class instance.
     */
    public function __construct()
    {
    }

    /**
     * Analyzing and scoring function
     */
    public function analyze($file)
    {     
        try {
            $htmlContent = file_get_contents($file->getRealPath());
        } catch(\Exception $e) {
            Log::error($e->getMessage());
            throw new HttpResponseException(HttpResponse::errorResponse([], 'Error encountered while fetching file path.'));
        }
        
        try {
            $crawler = new Crawler($htmlContent);
        }catch(\Exception $e){
            Log::error($e->getMessage());
            throw new HttpResponseException(HttpResponse::errorResponse([], 'Error encountered while crawling the file passed.'));
        }
        
        $issues = [];
        $score = 100;
        $issueDetail = [
            'img' => 0,
            'headings' => 0,
            'inputs'    => 0
        ];

        $htmlLines = explode("\n", $htmlContent);
       
        // Check for missing alt attributes in images
        try {
            $crawler->filter('img')->each(function ($node) use (&$issues, &$score, &$issueDetail, $htmlLines) {
                if (!$node->attr('alt')) {
                    $lineNumber = $this->findLineNumber($node, $htmlLines);
                    $issues['img'][] = [
                        'element' => 'img',
                        'issue' => 'Missing alt attribute',
                        'suggested_fix' => 'Add a meaningful alt text.',
                        'line_number' => $lineNumber
                    ];
                    $score -= 10;
                    $issueDetail['img']++;
                }
            });
        } catch(\Exception $e) {
            Log::error($e->getMessage());
            throw new HttpResponseException(HttpResponse::errorResponse([], 'Error encountered while crawling the file passed.'));
        }
        

        // Check for skipped heading levels
        try {
            $headings = $crawler->filter('h1, h2, h3, h4, h5, h6')->each(function ($node) {
                return intval(substr($node->nodeName(), 1));
            });
        
            if (!empty($headings)) {
                sort($headings);
                for ($i = 0; $i < count($headings) - 1; $i++) {
                    if ($headings[$i + 1] - $headings[$i] > 1) {
                        $lineNumber = $this->findLineNumber($crawler->filter("h{$headings[$i + 1]}")->first(), $htmlLines);
                        $issues['heading'][] = [
                            'element' => 'heading',
                            'issue' => 'Skipped heading level',
                            'suggested_fix' => 'Ensure headings follow a logical order.',
                            'line_number' => $lineNumber
                        ];
                        $score -= 5;
                        $issueDetail['headings']++;
                    }
                }
            }
        } catch(\Exception $e){
            Log::error($e->getMessage());
            throw new HttpResponseException(HttpResponse::errorResponse([], 'Error encountered while crawling the file passed.'));
        }
        
    
        // Check for missing labels on form elements
        try{
            $crawler->filter('input, select, textarea')->each(function ($node) use (&$issues, &$score, $crawler, &$issueDetail, $htmlLines) {
                if (!$node->attr('id')) {
                    return;
                }
            
                $label = $crawler->filter("label[for='{$node->attr('id')}']");
                if ($label->count() === 0) {
                    $lineNumber = $this->findLineNumber($node, $htmlLines);
                    $issues['inputs'][] = [
                        'element' => $node->nodeName(),
                        'issue' => 'Missing associated label',
                        'suggested_fix' => 'Ensure each form element has an associated <label>.',
                        'line_number' => $lineNumber
                    ];
                    $score -= 7;
                    $issueDetail['inputs']++;
                }
            });
        } catch(\Exception $e){
            Log::error($e->getMessage());
            throw new HttpResponseException(HttpResponse::errorResponse([], 'Error encountered while crawling the file passed.'));
        }
        
        Log::info('Analysis done', $issueDetail);
        return [
            'details'   => $issueDetail,
            'score' => max($score, 0),
            'issues' => $issues,
        ];
    
    }

    /**
     * Finds the line number of a given node in the original HTML.
     */
    private function findLineNumber($node, $htmlLines)
    {
        $htmlString = $node->outerHtml(); // Get the outer HTML of the element

        foreach ($htmlLines as $index => $line) {
            if (strpos($line, trim($htmlString)) !== false) {
                return $index + 1; // Line numbers start at 1
            }
        }

        return 'Unknown'; // If not found
    }
}