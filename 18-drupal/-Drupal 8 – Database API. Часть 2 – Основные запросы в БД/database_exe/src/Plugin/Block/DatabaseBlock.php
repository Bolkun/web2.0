<?php
/**
 * @file
 * Contains \Drupal\database_ex\Plugin\Block\DatabaseBlock.
 */
namespace Drupal\database_ex\Plugin\Block;

use Drupal\Core\Block\BlockBase;
/**
 * Provides a database_ex.
 *
 * @Block(
 *   id = "database_ex",
 *   admin_label = @Translation("Database Ex Block"),
 *   category = @Translation("Custom block example database")
 * )
 */
class DatabaseBlock extends BlockBase {


  public function testDataBase(){

    # Выборка единственной записи
    /*
    $query = \Drupal::database()->select('users_field_data', 'ufd');
    $query->addField('ufd', 'mail');
    $query->condition('ufd.uid', 1);
    $mail = $query->execute()->fetchField();
    return $mail;
    */

    # Выборка нескольких записей
    /*
    $query = \Drupal::database()->select('users_field_data', 'ufd');
    $query->fields('ufd', array('langcode', 'name', 'mail', 'created'));
    $query->condition('ufd.uid', 1);
    $output = $query->execute()->fetchAssoc();
    return $output;
    */

    # Использование db like
    # с учётом того, что заголовок страницы имеет название "Контакты"
    /*
    $query = \Drupal::database()->select('node_field_data', 'nfd');
    $query->fields('nfd', array('nid', 'title', 'status', 'created'));
    $query->condition('nfd.type', 'page');
    $query->condition('nfd.title', $query->escapeLike('Ко') . '%', 'LIKE');
    $output = $query->execute()->fetchAllKeyed();
    return $output;
    */

    # Использование JOIN
    /*
    $query = \Drupal::database()->select('node_field_data', 'nfd');
    $query->fields('nfd', array('nid', 'title', 'status', 'created'));
    $query->addField('ufd', 'name');
    $query->addField('ufd', 'uid');
    $query->join('users_field_data', 'ufd', 'ufd.uid = nfd.uid');
    $query->condition('nfd.type', 'page');
    $output = $query->execute()->fetchAllAssoc('nid');
    return $output;
    */

    # Вставка данных в таблицу БД
    /*
    $query = \Drupal::database()->insert('database_ex');
    $query->fields(array(
      'uid',
      'text',
      'timestamp',
    ));
    $query->values(array(
      '1',
      'My custom text for example database',
      time(),
    ));
    $query->execute();
    */

    # Обновление данных в таблице БД
    /*
    $query = \Drupal::database()->update('database_ex');
    $query->fields(array(
      'text' => 'My custom new text from update'
    ));
    $query->condition('id', 1);
    $query->execute();
    */

    # Обновление данных в таблице БД, с проверкой на уникальность ключа
    /*
    $query = \Drupal::database()->upsert('database_ex');
    $query->fields(array(
      'id',
      'uid',
      'text',
      'timestamp',
    ));
    $query->values(array(
      1,
      1,
      'Upsert custom text',
      time(),
    ));
    $query->key('id');
    $query->execute();
    */

    # Удаление записи из БД
    /*
    $query = \Drupal::database()->delete('database_ex');
    $query->condition('id', '1');
    $query->execute();
    */

    # Выборка с сортировкой по полю
    /*
    $query = \Drupal::database()->select('database_ex', 'de');
    $query->fields('de', array('id', 'uid', 'text', 'timestamp'));
    $query->orderBy('de.id', 'DESC');
    $output = $query->execute()->fetchAll();
    return $output;
    */

    # Выбрать определенной кол-во записей
    
    $query = \Drupal::database()->select('database_ex', 'de');
    $query->fields('de', array('id', 'uid', 'text', 'timestamp'));
    $query->orderBy('de.id', 'DESC');
    $query->range(0, 1);
    $output = $query->execute()->fetchAll();
    return $output;


    # Использование static запроса
   # $query = db_query("SELECT * FROM {node} WHERE nid IN (1)");
    #$output = $query->fetchAll();
    #return $output;





  }


  /**
   * {@inheritdoc}
   */
  public function build() {

    $output = $this->testDataBase();
    kint($output);

    return array(
      '#type' => 'markup',
      '#markup' => 'Database content example',
    );
  }
}