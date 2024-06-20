<?php

namespace App\Http\Controllers;

use Gemini\Laravel\Facades\Gemini;
use Illuminate\Http\Request;
use OpenAI;

class StreamingChatController extends Controller
{
    /**
     * For sending server event
     */
    private function send($event, $data)
    {
        echo "event: {$event}\n";
        echo 'data: '.$data;
        echo "\n\n";
        ob_flush();
        flush();
    }

    public function Gemini(Request $request)
    {
        $question = $request->query('question');

        return response()->stream(
            function () use (
                $question
            ) {
                $result = Gemini::geminiPro()->generateContent($question);

                $this->send('update', json_encode([
                    'text' => $result->text(),
                ]));
                $this->send('update', '<END_STREAMING_SSE>');
                logger($result->toArray());
            },
            200,
            [
                'Cache-Control' => 'no-cache',
                'Connection' => 'keep-alive',
                'X-Accel-Buffering' => 'no',
                'Content-Type' => 'text/event-stream',
            ]
        );
    }

    public function index()
    {
        return view('panel.Chat.index');
    }

    public function ChatGPT(Request $request)
    {
        $question = $request->query('question');

        return response()->stream(
            function () use (
                $question
            ) {
                $client = OpenAI::client(config('services.openai.key'));
                $response = $client->models()->list();
                //print_r($response);

                $result = $client->chat()->create([
                    'model' => config('services.openai.model'),
                    'messages' => [
                        ['role' => 'user', 'content' => $question],
                    ],
                ]);

                $this->send('update', json_encode([
                    'text' => $result->choices[0]->message->content,
                ]));
                $this->send('update', '<END_STREAMING_SSE>');
                logger($result->toArray());
            },
            200,
            [
                'Cache-Control' => 'no-cache',
                'Connection' => 'keep-alive',
                'X-Accel-Buffering' => 'no',
                'Content-Type' => 'text/event-stream',
            ]
        );
    }
}
