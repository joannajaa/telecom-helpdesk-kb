<?php
function parseTagNames(string $value): array
{
    $names = [];
    foreach (explode(',', $value) as $name) {
        $name = trim($name);
        if ($name === '') {
            continue;
        }

        $name = mb_strtolower($name);
        if (mb_strlen($name) > 50) {
            $name = mb_substr($name, 0, 50);
        }
        $names[$name] = true;
    }

    return array_keys($names);
}

function syncArticleTags(PDO $pdo, int $articleId, array $tagNames): void
{
    $pdo->prepare('DELETE FROM article_tags WHERE article_id = ?')->execute([$articleId]);
    $insertTag = $pdo->prepare('INSERT IGNORE INTO tags (name) VALUES (?)');
    $findTag = $pdo->prepare('SELECT id FROM tags WHERE name = ?');
    $insertArticleTag = $pdo->prepare('INSERT INTO article_tags (article_id, tag_id) VALUES (?, ?)');

    foreach ($tagNames as $tagName) {
        $insertTag->execute([$tagName]);
        $findTag->execute([$tagName]);
        $tagId = $findTag->fetchColumn();
        if ($tagId !== false) {
            $insertArticleTag->execute([$articleId, (int)$tagId]);
        }
    }
}

function getArticleTags(PDO $pdo, int $articleId): array
{
    $stmt = $pdo->prepare(
        'SELECT t.name
         FROM tags t
         JOIN article_tags at ON at.tag_id = t.id
         WHERE at.article_id = ?
         ORDER BY t.name ASC'
    );
    $stmt->execute([$articleId]);
    return $stmt->fetchAll(PDO::FETCH_COLUMN);
}
