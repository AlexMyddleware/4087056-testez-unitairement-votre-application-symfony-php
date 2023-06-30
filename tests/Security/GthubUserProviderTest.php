<?php

use App\Entity\User;
use GuzzleHttp\Client;
use PHPUnit\Framework\TestCase;
use App\Security\GithubUserProvider;
use JMS\Serializer\SerializerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;

class GithubUserProviderTest extends TestCase
{
    private Client | MockObject $client;
    private SerializerInterface | MockObject $serializer;
    private StreamInterface | MockObject $streamedResponse;
    private ResponseInterface | MockObject $response;

    protected function setUp(): void
    {
        $this->client = $this->createMock(Client::class);
        $this->serializer = $this->createMock(SerializerInterface::class);
        $this->streamedResponse = $this->createMock(StreamInterface::class);
        $this->response = $this->createMock(ResponseInterface::class);
    }

    public function testLoadUserByUsernameReturningAUser()
    {
        // Arrange
        $userData = ['login' => 'a login', 'name' => 'user name', 'email' => 'adress@mail.com', 'avatar_url' => 'url to the avatar', 'html_url' => 'url to profile'];
        
        $this->streamedResponse->expects($this->once())->method('getContents')->willReturn('foo');
        $this->response->expects($this->once())->method('getBody')->willReturn($this->streamedResponse);
        $this->client->expects($this->once())->method('get')->willReturn($this->response);
        $this->serializer->expects($this->once())->method('deserialize')->willReturn($userData);
        
        // Act
        $githubUserProvider = new GithubUserProvider($this->client, $this->serializer);
        $user = $githubUserProvider->loadUserByUsername('an-access-token');

        // Assert
        $this->assertInstanceOf(User::class, $user);
        $this->assertEquals('App\Entity\User', get_class($user));
        $expectedUser = new User($userData['login'], $userData['name'], $userData['email'], $userData['avatar_url'], $userData['html_url']);
        $this->assertEquals($expectedUser, $user);
    }

    public function testLoadUserByUsernameReturningAUserNoUserData()
    {
        // Arrange
        $userData = [];

        $this->streamedResponse->expects($this->once())->method('getContents')->willReturn('foo');
        $this->response->expects($this->once())->method('getBody')->willReturn($this->streamedResponse);
        $this->client->expects($this->once())->method('get')->willReturn($this->response);
        $this->serializer->expects($this->once())->method('deserialize')->willReturn($userData);

        // Assert
        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('Did not managed to get your user info from Github.');

        // Act
        $githubUserProvider = new GithubUserProvider($this->client, $this->serializer);
        $user = $githubUserProvider->loadUserByUsername('an-access-token');
    }
}
