<?php

namespace Drupal\rest_api_access_token\Repository;

use Drupal\Core\Database\Connection;
use Drupal\rest_api_access_token\Exception\TokenNotFoundException;
use Drupal\rest_api_access_token\Model\Token;
use Drupal\user\Entity\User;

/**
 * Repository class for Token.
 *
 * @package Drupal\rest_api_access_token\Repository
 */
class TokenRepository {

  /**
   * Database connection.
   *
   * @var \Drupal\Core\Database\Connection
   *   Database connection.
   */
  protected $connection;

  /**
   * TokenRepository constructor.
   *
   * @param \Drupal\Core\Database\Connection $connection
   *   Database connection.
   */
  public function __construct(Connection $connection) {
    $this->connection = $connection;
  }

  /**
   * Get Token model from database by user token.
   *
   * @param string $token
   *   User token.
   *
   * @return \Drupal\rest_api_access_token\Model\Token
   *   Return Token model.
   *
   * @throws \Drupal\rest_api_access_token\Exception\TokenNotFoundException
   *   Not found exception.
   */
  public function getByPublicToken(string $token) {
    $tokenRaw = $this->connection
      ->select(Token::TABLE_NAME, 't')
      ->fields('t')
      ->condition('public_token', $token)
      ->execute()
      ->fetchObject();

    if (empty($tokenRaw)) {
      throw new TokenNotFoundException($token);
    }
    $createdAt = new \DateTime();
    $createdAt->setTimestamp((int) $tokenRaw->created_at);
    $refreshedAt = new \DateTime();
    $refreshedAt->setTimestamp((int) $tokenRaw->refreshed_at);
    return new Token($tokenRaw->public_token, $tokenRaw->secret, $tokenRaw->user_id, $createdAt, $refreshedAt);
  }

  /**
   * Insert Token model into database.
   *
   * @param \Drupal\rest_api_access_token\Model\Token $accessToken
   *   Token model.
   *
   * @return int
   *   Id in database after insert.
   *
   * @throws \Exception
   *   Database exception.
   */
  public function insert(Token $accessToken) {
    return (int) $this->connection
      ->insert(Token::TABLE_NAME)
      ->fields([
        'user_id' => $accessToken->getUserId(),
        'public_token' => $accessToken->getPublic(),
        'secret' => $accessToken->getSecret(),
        'created_at' => time(),
        'refreshed_at' => time(),
      ])
      ->execute();
  }

  /**
   * Remove Token model.
   *
   * @param string $token
   *   User token.
   *
   * @return int
   *   Id in database.
   */
  public function removeByPublicToken(string $token) {
    return $this->connection->delete(Token::TABLE_NAME)
      ->condition('public_token', $token)
      ->execute();
  }

  /**
   * Remove Token model by User.
   *
   * @param \Drupal\user\Entity\User $user
   *   User model.
   *
   * @return int
   *   Id in database.
   */
  public function removeByUser(User $user) {
    return $this->connection->delete(Token::TABLE_NAME)
      ->condition('user_id', $user->id())
      ->execute();
  }

  /**
   * Remove other user tokens.
   *
   * @param \Drupal\rest_api_access_token\Model\Token $accessToken
   *
   * @return int
   */
  public function removeOtherUserTokens(Token $accessToken) {
    return $this->connection->delete(Token::TABLE_NAME)
      ->condition('user_id', $accessToken->getUserId())
      ->condition('public_token', $accessToken->getPublic(), '<>')
      ->execute();
  }

  /**
   * @param \Drupal\rest_api_access_token\Model\Token $accessToken
   *
   * @return int
   */
  public function refresh(Token $accessToken) {
    return (int) $this->connection
      ->update(Token::TABLE_NAME)
      ->fields([
        'refreshed_at' => time(),
      ])
      ->condition('public_token', $accessToken->getPublic())
      ->condition('user_id', $accessToken->getUserId())
      ->execute();
  }

  /**
   * @param \DateTime $date
   *
   * @return int
   */
  public function removeExpired(\DateTime $date) {
    return $this->connection->delete(Token::TABLE_NAME)
      ->condition('refreshed_at', $date->getTimestamp(), '<')
      ->execute();
  }

}
