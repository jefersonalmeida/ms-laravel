<?php

namespace Tests\Traits;

use Exception;
use Illuminate\Http\Response;
use Illuminate\Testing\TestResponse;

trait TestSaves
{
    /**
     * @throws Exception
     */
    protected function assertStore(array $data = [], array $dbData = [], array $jsonData = null): TestResponse
    {
        /** @var TestResponse $response */
        $response = $this->json('POST', $this->routeStore(), $data);

        if ($response->status() !== Response::HTTP_CREATED) {
            throw new Exception(sprintf(
                'Response status must be %s, given %s\n: %s',
                Response::HTTP_CREATED,
                $response->status(),
                $response->content()
            ));
        }
        $this->assertInDatabase($dbData, $response);
        $this->assertJsonResponseContent($jsonData, $dbData, $response);
        return $response;
    }

    /**
     * @param array $dbData
     * @param TestResponse $response
     */
    private function assertInDatabase(array $dbData, TestResponse $response): void
    {
        $model = $this->model();
        $table = (new $model)->getTable();
        $this->assertDatabaseHas($table, $dbData + ['id' => $response->json('id')]);
    }

    /**
     * @param array|null $jsonData
     * @param array $dbData
     * @param TestResponse $response
     */
    private function assertJsonResponseContent(?array $jsonData, array $dbData, TestResponse $response): void
    {
        $testResponse = $jsonData ?? $dbData;
        $response->assertJsonFragment($testResponse + ['id' => $response->json('id')]);
    }

    /**
     * @throws Exception
     */
    protected function assertUpdate(array $data = [], array $dbData = [], array $jsonData = null): TestResponse
    {
        /** @var TestResponse $response */
        $response = $this->json('PUT', $this->routeUpdate(), $data);

        if ($response->status() !== Response::HTTP_OK) {
            throw new Exception(sprintf(
                'Response status must be %s, given %s\n: %s',
                Response::HTTP_OK,
                $response->status(),
                $response->content()
            ));
        }
        $this->assertInDatabase($dbData, $response);
        $this->assertJsonResponseContent($jsonData, $dbData, $response);
        return $response;
    }
}
