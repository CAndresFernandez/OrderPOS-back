<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230922094626 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE closed_order CHANGE items items LONGTEXT NOT NULL COMMENT \'(DC2Type:json)\'');
        $this->addSql('ALTER TABLE `order` DROP FOREIGN KEY FK_F529939873B8532F');
        $this->addSql('DROP INDEX UNIQ_F529939873B8532F ON `order`');
        $this->addSql('ALTER TABLE `order` CHANGE related_table related_table_id INT NOT NULL');
        $this->addSql('ALTER TABLE `order` ADD CONSTRAINT FK_F52993984A7C66F1 FOREIGN KEY (related_table_id) REFERENCES `table` (id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_F52993984A7C66F1 ON `order` (related_table_id)');
        $this->addSql('ALTER TABLE user CHANGE roles roles LONGTEXT NOT NULL COMMENT \'(DC2Type:json)\'');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE `order` DROP FOREIGN KEY FK_F52993984A7C66F1');
        $this->addSql('DROP INDEX UNIQ_F52993984A7C66F1 ON `order`');
        $this->addSql('ALTER TABLE `order` CHANGE related_table_id related_table INT NOT NULL');
        $this->addSql('ALTER TABLE `order` ADD CONSTRAINT FK_F529939873B8532F FOREIGN KEY (related_table) REFERENCES `table` (id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_F529939873B8532F ON `order` (related_table)');
        $this->addSql('ALTER TABLE closed_order CHANGE items items LONGTEXT NOT NULL COLLATE `utf8mb4_bin`');
        $this->addSql('ALTER TABLE user CHANGE roles roles LONGTEXT NOT NULL COLLATE `utf8mb4_bin`');
    }
}
