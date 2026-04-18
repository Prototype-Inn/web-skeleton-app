<?php

declare(strict_types=1);

namespace PrototypeIn\Tests\Action;

use Mockery;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use PrototypeIn\App\Action\DemoAction;
use PrototypeIn\App\Domain\Model\User;
use PrototypeIn\App\Domain\Repository\UserRepository;
use PrototypeIn\App\Domain\Service\PasswordService;
use PrototypeIn\App\Form\DemoForm;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;

class DemoActionTest extends MockeryTestCase
{
    private UserRepository|Mockery\MockInterface $userRepository;
    private PasswordService|Mockery\MockInterface $passwordService;
    private DemoForm|Mockery\MockInterface $demoForm;
    private ServerRequestInterface|Mockery\MockInterface $request;
    private DemoAction $demoAction;

    protected function setUp(): void
    {
        parent::setUp();
        $this->userRepository = Mockery::mock(UserRepository::class);
        $this->passwordService = Mockery::mock(PasswordService::class);
        $this->demoForm = Mockery::mock(DemoForm::class);
        $this->request = Mockery::mock(ServerRequestInterface::class);

        $this->demoAction = new DemoAction(
            $this->userRepository,
            $this->passwordService,
            $this->demoForm
        );
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function testShowReturnsJsonResponseWithFormSchema(): void
    {
        $response = $this->demoAction->show($this->request, []);
        $this->assertInstanceOf(ResponseInterface::class, $response);
        $this->assertEquals(200, $response->getStatusCode());

        $expectedJson = json_encode([
            'form' => [
                'action' => '/demo',
                'method' => 'POST',
                'fields' => [
                    'email' => ['type' => 'email', 'label' => 'Email', 'required' => true],
                    'password' => ['type' => 'password', 'label' => 'Password', 'required' => true],
                    'firstName' => ['type' => 'text', 'label' => 'First Name', 'required' => false],
                    'lastName' => ['type' => 'text', 'label' => 'Last Name', 'required' => false],
                ],
            ],
        ]);
        $this->assertJsonStringEqualsJsonString($expectedJson, (string) $response->getBody());
    }

    public function testSubmitReturnsValidationErrorsWhenFormIsInvalid(): void
    {
        $requestBody = '{"email": "invalid-email"}';
        $stream = Mockery::mock(StreamInterface::class);
        $stream->shouldReceive('__toString')->andReturn($requestBody);

        $this->request->shouldReceive('getBody')->andReturn($stream);

        $this->demoForm->shouldReceive('setData')->once()->with(['email' => 'invalid-email']);
        $this->demoForm->shouldReceive('isValid')->once()->andReturn(false);
        $this->demoForm->shouldReceive('getMessages')->once()->andReturn([
            'email' => ['Email is not valid'],
            'password' => ['Password is required'],
        ]);
        $this->demoForm->shouldReceive('getData')->andReturn(['email' => 'invalid-email']);

        $response = $this->demoAction->submit($this->request, []);

        $this->assertEquals(422, $response->getStatusCode());
        $responseBody = json_decode((string) $response->getBody(), true);

        $this->assertFalse($responseBody['success']);
        $this->assertEquals('Validation failed', $responseBody['message']);
        $this->assertArrayHasKey('viewModel', $responseBody);
        $this->assertArrayHasKey('errors', $responseBody['viewModel']);
        $this->assertEquals('Email is not valid', $responseBody['viewModel']['errors']['email']);
        $this->assertEquals('Password is required', $responseBody['viewModel']['errors']['password']);
    }

    public function testSubmitReturnsErrorWhenEmailAlreadyRegistered(): void
    {
        $requestBody = '{"email": "existing@example.com", "password": "password123"}';
        $stream = Mockery::mock(StreamInterface::class);
        $stream->shouldReceive('__toString')->andReturn($requestBody);
        $this->request->shouldReceive('getBody')->andReturn($stream);

        $this->demoForm->shouldReceive('setData')->once()->with(Mockery::subset(['email' => 'existing@example.com']));
        $this->demoForm->shouldReceive('isValid')->once()->andReturn(true);
        $this->demoForm->shouldReceive('getData')->andReturn(['email' => 'existing@example.com', 'password' => 'password123']);

        $existingUser = new User();
        $this->userRepository->shouldReceive('findByEmail')->once()->with('existing@example.com')->andReturn($existingUser);

        $response = $this->demoAction->submit($this->request, []);

        $this->assertEquals(409, $response->getStatusCode());
        $responseBody = json_decode((string) $response->getBody(), true);

        $this->assertFalse($responseBody['success']);
        $this->assertEquals('Email already registered', $responseBody['message']);
        $this->assertArrayHasKey('viewModel', $responseBody);
        $this->assertArrayHasKey('errors', $responseBody['viewModel']);
        $this->assertEquals('Email already registered', $responseBody['viewModel']['errors']['email']);
    }

    public function testSubmitSuccessfullyRegistersNewUser(): void
    {
        $requestBody = '{"email": "new@example.com", "password": "securepassword", "firstName": "John", "lastName": "Doe"}';
        $stream = Mockery::mock(StreamInterface::class);
        $stream->shouldReceive('__toString')->andReturn($requestBody);
        $this->request->shouldReceive('getBody')->andReturn($stream);

        $this->demoForm->shouldReceive('setData')->once()->with(Mockery::subset(['email' => 'new@example.com']));
        $this->demoForm->shouldReceive('isValid')->once()->andReturn(true);
        $this->demoForm->shouldReceive('getData')->andReturn([
            'email' => 'new@example.com',
            'password' => 'securepassword',
            'firstName' => 'John',
            'lastName' => 'Doe'
        ]);

        $this->userRepository->shouldReceive('findByEmail')->once()->with('new@example.com')->andReturn(null);
        $this->passwordService->shouldReceive('hash')->once()->with('securepassword')->andReturn('hashed_password');
        $this->userRepository->shouldReceive('save')->once()->with(Mockery::type(User::class));

        $response = $this->demoAction->submit($this->request, []);

        $this->assertEquals(201, $response->getStatusCode());
        $responseBody = json_decode((string) $response->getBody(), true);

        $this->assertTrue($responseBody['success']);
        $this->assertEquals('Form submitted successfully', $responseBody['message']);
        $this->assertArrayHasKey('viewModel', $responseBody);
        $this->assertEmpty($responseBody['viewModel']['errors']);
        $this->assertEquals('new@example.com', $responseBody['viewModel']['data']['email']);
    }
}
