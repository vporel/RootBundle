<?php
namespace RootBundle\Service;

use ChatBundle\Entity\ChatMessage;
use NotificationBundle\Entity\Notification;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpClient\Exception\TransportException;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

/**
 * Interface with the node application
 * Exclusively with json format
 * @author Vivian NKOUANANG (https://github.com/vporel) <dev.vporel@gmail.com>
 */
class NodeApp{
    private const HOSTS = [
        "dev" => "http://localhost:3000",
        "prod" => "https://node-app.lahotte.net"
    ];
    /**
     * @var string
     */
    private $host;

    /**
     * The application part that is using the service
     * @var string
     */
    private $app;

    /**
     * The key used to communicate with the node server
     * @var string
     */
    private $nodeServerKey;

    public function __construct(private HttpClientInterface $httpClient, private SerializerInterface $serializer, ParameterBagInterface $parameterBag){
        $this->host = self::HOSTS[$_ENV["APP_ENV"]];
        $this->nodeServerKey = $_ENV["NODE_SERVER_KEY"];
        $this->app = $parameterBag->get("app_key_name");
    }

    public function sendEmail(array $receivers, string $subject, string $body): bool{
        return $this->request("POST", "/send-email", [
            "receivers" => $receivers,
            "subject" => $subject,
            "body" =>$body
        ]) != null;
        
    }

    public function sendNotification(Notification $notification): bool{
        return $this->request("POST", "/send-notification", [
            "userId" => $notification->getUser()->getId(),
            "notification" => json_decode($this->serializer->serialize($notification, "json", ["groups" => ["default", "notification:read:collection"]]), true)
        ]) != null;
    }

    /**
     * Falsh notification. Can be sent to many users at once
     *
     * @param array $usersGroups Only for the users that implement the NodeAppUserInterface
     * @param array $data
     */
    public function sendFlashNotification(array $usersRooms, array $data): bool{
        return $this->request("POST", "/send-flash-notification", [
            "rooms" => $usersRooms,
            "notification" => $data
        ]) != null;
    }

    public function sendChatMessage($message): bool{
        $messageArray = json_decode($this->serializer->serialize($message, "json", ["groups" => ["default", "chat_message:read:collection", "chat_message:read:with_chat", "user:read:simplified"]]), true);
        $messageArray["url"] = "/?openChat=1&chatId=".$message->getChat()->getId();
        return $this->request("POST", "/send-chat-message", [
            "message" => $messageArray
        ]) != null;
    }

    public function updateFaqChatBot(): bool{
        return $this->request("POST", "/faq-update-chat-bot") != null;
    }

    /**
     * Use only json format
     * @param string $method
     * @param string $path
     * @param array $data
     * @return mixed Response content if every goes well
     */
    private function request(string $method, string $path, array $data = []): mixed{
        try{
            $data["app"] = $this->app;
            $data["key"] = $this->nodeServerKey;
            $response = $this->httpClient->request($method, $this->host.$path, ["json" => $data, 'timeout' => 2]);
            return ($response->getStatusCode() == 200 && $response->toArray()["status"] == 1)
                ? $response->getContent()
                : null
            ;
        }catch(TransportException $e){
            return null;
        }
    }
}