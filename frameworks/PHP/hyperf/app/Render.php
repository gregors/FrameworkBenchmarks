<?php

namespace App;


use Hyperf\HttpMessage\Stream\SwooleStream;
use Hyperf\View\Engine\EngineInterface;
use Hyperf\View\Mode;

class Render extends \Hyperf\View\Render
{
    public function render(string $template, array $data)
    {
        switch ($this->mode) {
            case Mode::SYNC:
                /** @var EngineInterface $engine */
                $engine = $this->container->get($this->engine);
                $result = $engine->render($template, $data, $this->config);
                break;
            case Mode::TASK:
            default:
                $executor = $this->container->get(TaskExecutor::class);
                $result = $executor->execute(new Task([$this->engine, 'render'], [$template, $data, $this->config]));
        }

        return $this->response()->withAddedHeader('content-type', 'text/html; charset=utf-8')->withBody(new SwooleStream($result));
    }


}