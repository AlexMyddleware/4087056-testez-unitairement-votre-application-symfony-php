<?php


// write an elaborate test class for the class GithubUserProvider

use PHPUnit\Framework\TestCase;
use App\Entity\User;
use App\Security\GithubUserProvider;
use GuzzleHttp\Client;
use JMS\Serializer\SerializerInterface;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Exception\GuzzleException;

class GithubUserProviderTest extends TestCase
{

    public function testLoadUserByUsernameReturningAUser()
    {
        // --------------- 1. Arrange ---------------
        $streamedResponse = $this
        // get a mock builder for the StreamInterface class
        ->getMockBuilder('Psr\Http\Message\StreamInterface')
        ->getMock();

        $streamedResponse
        ->expects($this->once()) // Nous nous attendons à ce que la méthode getContents soit appelée une fois
        ->method('getContents')
        ->willReturn('foo');
        
        // create a mock of the ResponseInterface class
        $response = $this
            // get a mock builder for the ResponseInterface class
            ->getMockBuilder('Psr\Http\Message\ResponseInterface')
            ->getMock();

        $response
        ->expects($this->once()) // Nous nous attendons à ce que la méthode getBody soit appelée une fois
        ->method('getBody')
        ->willReturn($streamedResponse);

        // create a mock of the Client class by guzzlehttp
        $client = $this->getMockBuilder('GuzzleHttp\Client')
            // disable the constructor
            ->disableOriginalConstructor()
            // create a mock object of the Client class
            ->getMock();

        // make sure that the method get will return a response
        $client
        ->expects($this->once()) // Nous nous attendons à ce que la méthode get soit appelée une fois
            ->method('get')
            ->willReturn($response);

        // create a mock of the SerializerInterface class
        $serializer = $this
            // get a mock builder for the SerializerInterface class
            ->getMockBuilder('JMS\Serializer\Serializer')
            // disable the constructor
            ->disableOriginalConstructor()
            // get the mock object of the SerializerInterface class
            ->getMock();
            // get fake data for the deserializer
            $userData = ['login' => 'a login', 'name' => 'user name', 'email' => 'adress@mail.com', 'avatar_url' => 'url to the avatar', 'html_url' => 'url to profile'];
        $serializer
        ->expects($this->once()) // Nous nous attendons à ce que la méthode deserialize soit appelée une fois
        ->method('deserialize')
        ->willReturn($userData);
        
        // --------------- 2. Act ---------------
        
        // var anotation that it's okay to get a mock client and serializer
        /** @var Client $client */
        /** @var SerializerInterface $serializer */
        $githubUserProvider = new GithubUserProvider($client, $serializer);
        $user = $githubUserProvider->loadUserByUsername('an-access-token');
    

        // --------------- 3. Assert ---------------

        // assert that the $user is an instance of the User class

        // This checks if $user is an instance of User or an instance of a subclass of User. It is more flexible because it allows for polymorphism; that is, if you had a class Admin that extended User, $user could be an instance of Admin and this test would still pass.
        $this->assertInstanceOf(User::class, $user);
        
        // expect that the $user is an instance of the User class
        
        // This checks if the exact class of $user is User. It does not allow for polymorphism; that is, if $user were an instance of a subclass of User (like Admin from before), this test would fail, even though $user is technically an instance of User through inheritance.
        $this->assertEquals('App\Entity\User', get_class($user));
        
        // create an expected user object
        $expectedUser = new User($userData['login'], $userData['name'], $userData['email'], $userData['avatar_url'], $userData['html_url']);
        // expect that the $user is equal to the $expectedUser
        $this->assertEquals($expectedUser, $user);


    }

    public function testLoadUserByUsernameReturningAUserNoUserData()
    {
        // --------------- 1. Arrange ---------------
        $streamedResponse = $this
        // get a mock builder for the StreamInterface class
        ->getMockBuilder('Psr\Http\Message\StreamInterface')
        ->getMock();

        $streamedResponse
        ->expects($this->once()) // Nous nous attendons à ce que la méthode getContents soit appelée une fois
        ->method('getContents')
        ->willReturn('foo');
        
        // create a mock of the ResponseInterface class
        $response = $this
            // get a mock builder for the ResponseInterface class
            ->getMockBuilder('Psr\Http\Message\ResponseInterface')
            ->getMock();

        $response
        ->expects($this->once()) // Nous nous attendons à ce que la méthode getBody soit appelée une fois
        ->method('getBody')
        ->willReturn($streamedResponse);

        // create a mock of the Client class by guzzlehttp
        $client = $this->getMockBuilder('GuzzleHttp\Client')
            // disable the constructor
            ->disableOriginalConstructor()
            // create a mock object of the Client class
            ->getMock();

        // make sure that the method get will return a response
        $client
        ->expects($this->once()) // Nous nous attendons à ce que la méthode get soit appelée une fois
            ->method('get')
            ->willReturn($response);

        // create a mock of the SerializerInterface class
        $serializer = $this
            // get a mock builder for the SerializerInterface class
            ->getMockBuilder('JMS\Serializer\Serializer')
            // disable the constructor
            ->disableOriginalConstructor()
            // get the mock object of the SerializerInterface class
            ->getMock();
            // get fake data for the deserializer
            $userData = [];
        $serializer
        ->expects($this->once()) // Nous nous attendons à ce que la méthode deserialize soit appelée une fois
        ->method('deserialize')
        ->willReturn($userData);
        
        // --------------- 3. Assert ---------------
        
        // expect an exception because we don't obtain any user data
        $this->expectException(LogicException::class);
        
        // expect an exception message
        $this->expectExceptionMessage('Did not managed to get your user info from Github.');

        // --------------- 2. Act ---------------
        
        // var anotation that it's okay to get a mock client and serializer
        /** @var Client $client */
        /** @var SerializerInterface $serializer */
        $githubUserProvider = new GithubUserProvider($client, $serializer);
        $user = $githubUserProvider->loadUserByUsername('an-access-token');
    }
}
