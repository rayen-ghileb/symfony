<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260403155914 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE comments CHANGE deleted_at deleted_at DATETIME DEFAULT NULL');
        $this->addSql('ALTER TABLE comments ADD CONSTRAINT FK_5F9E962AF675F31B FOREIGN KEY (author_id) REFERENCES user (id)');
        $this->addSql('CREATE INDEX IDX_5F9E962AF675F31B ON comments (author_id)');
        $this->addSql('ALTER TABLE post_media CHANGE media_type media_type VARCHAR(10) DEFAULT \'IMAGE\' NOT NULL, CHANGE created_at created_at DATETIME DEFAULT NULL');
        $this->addSql('ALTER TABLE posts CHANGE media_url media_url VARCHAR(255) DEFAULT NULL, CHANGE visibility visibility VARCHAR(20) DEFAULT \'PATIENTS_ONLY\' NOT NULL, CHANGE deleted_at deleted_at DATETIME DEFAULT NULL');
        $this->addSql('ALTER TABLE posts ADD CONSTRAINT FK_885DBAFAF675F31B FOREIGN KEY (author_id) REFERENCES user (id)');
        $this->addSql('CREATE INDEX IDX_885DBAFAF675F31B ON posts (author_id)');
        $this->addSql('ALTER TABLE reactions ADD CONSTRAINT FK_38737FB3A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('CREATE INDEX IDX_38737FB3A76ED395 ON reactions (user_id)');
        $this->addSql('ALTER TABLE user CHANGE phone_number phone_number VARCHAR(20) DEFAULT NULL, CHANGE address address VARCHAR(255) DEFAULT NULL, CHANGE role role VARCHAR(20) DEFAULT \'PATIENT\' NOT NULL, CHANGE created_at created_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL, CHANGE profile_picture profile_picture VARCHAR(500) DEFAULT NULL, CHANGE gender gender VARCHAR(20) DEFAULT NULL, CHANGE date_of_birth date_of_birth DATE DEFAULT NULL, CHANGE emergency_contact emergency_contact VARCHAR(100) DEFAULT NULL, CHANGE specialization specialization VARCHAR(100) DEFAULT NULL, CHANGE license_number license_number VARCHAR(50) DEFAULT NULL, CHANGE google_id google_id VARCHAR(255) DEFAULT NULL, CHANGE auth_provider auth_provider VARCHAR(20) DEFAULT \'LOCAL\' NOT NULL');
        $this->addSql('ALTER TABLE messenger_messages CHANGE delivered_at delivered_at DATETIME DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE comments DROP FOREIGN KEY FK_5F9E962AF675F31B');
        $this->addSql('DROP INDEX IDX_5F9E962AF675F31B ON comments');
        $this->addSql('ALTER TABLE comments CHANGE deleted_at deleted_at DATETIME DEFAULT \'NULL\'');
        $this->addSql('ALTER TABLE messenger_messages CHANGE delivered_at delivered_at DATETIME DEFAULT \'NULL\'');
        $this->addSql('ALTER TABLE posts DROP FOREIGN KEY FK_885DBAFAF675F31B');
        $this->addSql('DROP INDEX IDX_885DBAFAF675F31B ON posts');
        $this->addSql('ALTER TABLE posts CHANGE media_url media_url VARCHAR(255) DEFAULT \'NULL\', CHANGE visibility visibility VARCHAR(20) DEFAULT \'\'\'PATIENTS_ONLY\'\'\' NOT NULL, CHANGE deleted_at deleted_at DATETIME DEFAULT \'NULL\'');
        $this->addSql('ALTER TABLE post_media CHANGE media_type media_type VARCHAR(10) DEFAULT \'\'\'IMAGE\'\'\' NOT NULL, CHANGE created_at created_at DATETIME DEFAULT \'NULL\'');
        $this->addSql('ALTER TABLE reactions DROP FOREIGN KEY FK_38737FB3A76ED395');
        $this->addSql('DROP INDEX IDX_38737FB3A76ED395 ON reactions');
        $this->addSql('ALTER TABLE user CHANGE phone_number phone_number VARCHAR(20) DEFAULT \'NULL\', CHANGE address address VARCHAR(255) DEFAULT \'NULL\', CHANGE role role VARCHAR(20) DEFAULT \'\'\'PATIENT\'\'\' NOT NULL, CHANGE created_at created_at DATETIME DEFAULT \'current_timestamp()\' NOT NULL, CHANGE profile_picture profile_picture VARCHAR(500) DEFAULT \'NULL\', CHANGE gender gender VARCHAR(20) DEFAULT \'NULL\', CHANGE date_of_birth date_of_birth DATE DEFAULT \'NULL\', CHANGE emergency_contact emergency_contact VARCHAR(100) DEFAULT \'NULL\', CHANGE specialization specialization VARCHAR(100) DEFAULT \'NULL\', CHANGE license_number license_number VARCHAR(50) DEFAULT \'NULL\', CHANGE google_id google_id VARCHAR(255) DEFAULT \'NULL\', CHANGE auth_provider auth_provider VARCHAR(20) DEFAULT \'\'\'LOCAL\'\'\' NOT NULL');
    }
}
