<?php

namespace Drupal\vass_core\Plugin\rest\resource;


use Drupal\Core\Session\AccountProxyInterface;
use Drupal\node\Entity\Node;
use Drupal\rest\Plugin\ResourceBase;
use Drupal\rest\ResourceResponse;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Psr\Log\LoggerInterface;
use Drupal\user\UserStorageInterface;
use Drupal\Core\Cache\CacheableMetadata;

/**
 * Quiz Resource
 *
 * @RestResource(
 *   id = "quiz_resource",
 *   label = @Translation("Quiz Resource"),
 *   uri_paths = {
 *     "canonical" = "/api/quiz",
 *     "https://www.drupal.org/link-relations/create" = "/api/quiz",
 *   }
 * )
 */
class QuizResource extends ResourceBase {
  /**
   * A current user instance.
   *
   * @var \Drupal\Core\Session\AccountProxyInterface
   */
  protected $currentUser;
  
  /**
   * The user storage.
   *
   * @var \Drupal\user\UserStorageInterface
   */
  protected $userStorage;
  
  /**
   * Constructs a new GetPasswordRestResourse object.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param array $serializer_formats
   *   The available serialization formats.
   * @param \Psr\Log\LoggerInterface $logger
   *   A logger instance.
   * @param \Drupal\Core\Session\AccountProxyInterface $current_user
   *   A current user instance.
   */
  public function __construct(
    array $configuration,
    $plugin_id,
    $plugin_definition,
    array $serializer_formats,
    LoggerInterface $logger,
    AccountProxyInterface $current_user,
    UserStorageInterface $user_storage) {
    parent::__construct($configuration, $plugin_id, $plugin_definition, $serializer_formats, $logger);
    $this->currentUser = $current_user;
    $this->userStorage = $user_storage;
    $this->logger = $logger;
  }
  
  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->getParameter('serializer.formats'),
      $container->get('logger.factory')->get('rest_password'),
      $container->get('current_user'),
      $container->get('entity.manager')->getStorage('user')
    );
  }
  
  /**
   * Responds to entity GET requests.
   * @return \Drupal\rest\ResourceResponse
   */
  public function get() {
    $code = 200;
    try {
      $query = \Drupal::entityQuery('node')
        ->condition('status', 1)
        ->condition('type', 'quiz')
        ->accessCheck(FALSE);
      
      $nids = $query->execute();
      
      $nodes = Node::loadMultiple($nids);
      $questions = array();
      
      foreach ($nodes as $nid => $node) {
        $options = array();
        foreach ($node->get('field_answer')->getValue() as $key => $value) {
          $options[$value['value']] = $value['value'];
        }
        $questions[$nid] = array(
          'title' => $node->getTitle(),
          'options' => $options,
        );
      }
      
      $response = [
        'data' => $questions
      ];
    }
    catch (\Exception $e) {
      $code = 400;
      $response = [
        'message' => $e->getMessage(),
      ];
    }
    
    $response = new ResourceResponse($response, $code);
    $disable_cache = new CacheableMetadata();
    $disable_cache->setCacheMaxAge(0);
    
    $response->addCacheableDependency($disable_cache);
    
    return $response;
  }
}
