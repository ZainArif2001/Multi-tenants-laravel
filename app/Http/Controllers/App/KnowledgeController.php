<?php

namespace App\Http\Controllers\App;

use App\Http\Controllers\Controller;
use App\Models\KnowledgeDoc;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class KnowledgeController extends Controller
{
    public function index(): View
    {
        return view('app.knowledge.index', ['docs' => KnowledgeDoc::latest()->paginate(12)]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'title' => 'required|string|max:200',
            'file' => 'required|file|mimes:txt,md,pdf,docx,csv|max:10240',
        ]);

        $file = $request->file('file');
        $path = $file->store('knowledge', 'local');
        $content = $this->extractText($file);

        abort_if(trim($content) === '', 422);

        KnowledgeDoc::create([
            'title' => $data['title'],
            'file_path' => $path,
            'content' => $content,
        ]);

        return back()->with('success', 'Document added to the AI knowledge base.');
    }

    public function destroy(KnowledgeDoc $doc): RedirectResponse
    {
        if ($doc->file_path) {
            Storage::disk('local')->delete($doc->file_path);
        }
        $doc->delete();

        return back()->with('success', 'Document removed.');
    }

    protected function extractText($file): string
    {
        $ext = strtolower($file->getClientOriginalExtension());
        $path = $file->getRealPath();

        return match ($ext) {
            'pdf' => rescue(fn () => (new \Smalot\PdfParser\Parser())->parseFile($path)->getText(), ''),
            'docx' => rescue(function () use ($path) {
                $zip = new \ZipArchive();
                if ($zip->open($path) !== true) return '';
                $xml = $zip->getFromName('word/document.xml');
                $zip->close();

                return $xml ? trim(preg_replace('/\s+/', ' ', strip_tags($xml))) : '';
            }, ''),
            default => trim(file_get_contents($path)),
        };
    }
}
