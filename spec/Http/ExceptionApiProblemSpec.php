<?php

declare(strict_types=1);

namespace spec\Phpro\ApiProblem\Http;

use Exception;
use Phpro\ApiProblem\DebuggableApiProblemInterface;
use Phpro\ApiProblem\Http\ExceptionApiProblem;
use Phpro\ApiProblem\Http\HttpApiProblem;
use PhpSpec\ObjectBehavior;

class ExceptionApiProblemSpec extends ObjectBehavior
{
    public function let(): void
    {
        $this->beConstructedWith(new Exception('message', 500));
    }

    public function it_is_initializable(): void
    {
        $this->shouldHaveType(ExceptionApiProblem::class);
    }

    public function it_is_an_http_api_problem(): void
    {
        $this->shouldHaveType(HttpApiProblem::class);
    }

    public function it_is_debuggable(): void
    {
        $this->shouldHaveType(DebuggableApiProblemInterface::class);
    }

    public function it_can_parse_to_array(): void
    {
        $this->toArray()->shouldBe([
            'status' => 500,
            'type' => HttpApiProblem::TYPE_HTTP_RFC,
            'title' => HttpApiProblem::getTitleForStatusCode(500),
            'detail' => 'message',
        ]);
    }

    public function it_uses_internal_error_as_general_status_code(): void
    {
        $this->beConstructedWith(new Exception('message'));

        $this->toArray()->shouldBe([
            'status' => 500,
            'type' => HttpApiProblem::TYPE_HTTP_RFC,
            'title' => HttpApiProblem::getTitleForStatusCode(500),
            'detail' => 'message',
        ]);
    }

    public function it_can_parse_to_debuggable_array(): void
    {
        $exception = new Exception('message', 500);
        $this->beConstructedWith($exception);

        $this->toDebuggableArray()->shouldBe([
            'status' => 500,
            'type' => HttpApiProblem::TYPE_HTTP_RFC,
            'title' => HttpApiProblem::getTitleForStatusCode(500),
            'detail' => 'message',
            'exception' => [
                'message' => 'message',
                'code' => 500,
                'trace' => $exception->getTraceAsString(),
                'previous' => [],
            ],
        ]);
    }

    public function it_contains_flattened_previous_exceptions_in_debuggable_output(): void
    {
        $first = new Exception('first', 1);
        $previous = new Exception('previous', 2, $first);
        $exception = new Exception('message', 0, $previous);
        $this->beConstructedWith($exception);

        $this->toDebuggableArray()->shouldBe([
            'status' => 500,
            'type' => HttpApiProblem::TYPE_HTTP_RFC,
            'title' => HttpApiProblem::getTitleForStatusCode(500),
            'detail' => 'message',
            'exception' => [
                'message' => 'message',
                'code' => 0,
                'trace' => $exception->getTraceAsString(),
                'previous' => [
                    [
                        'message' => 'previous',
                        'code' => 2,
                        'trace' => $previous->getTraceAsString(),
                    ],
                    [
                        'message' => 'first',
                        'code' => 1,
                        'trace' => $first->getTraceAsString(),
                    ],
                ],
            ],
        ]);
    }
}