<?php

$app->get('/1day/tutorial',function($request,$response,$args) {
    $req = $request->getQueryParams();
    $sql = 'select * from messages where user_id = :user_id';
    $con = $this->get('pdo');
    $sth = $con->prepare($sql);
    $sth->bindValue(':user_id', (int)$req['user_id'], PDO::PARAM_INT);
    $sth->execute();
    $results = $sth->fetchAll();
    return $this->view->render($response,'chapter2.twig',['messages' => $results]);
});

$app->get('/1day/chapter3-1/preprocessing',function($request,$response,$args) {
    $con = $this->get('pdo');

    $sth = $con->prepare('alter table messages add index user_id(user_id)');
    $sth->execute();
    $sth = $con->prepare('alter table follows add index user_id(user_id)');
    $sth->execute();
    $sth = $con->prepare('alter table follows add index follow_user_id(follow_user_id)');
    $sth->execute();
    echo "The preprocessing of chapter3-1 has been completed!";
});

$app->get('/1day/chapter3-1',function($request,$response,$args) {

    $id = mt_rand(1,100000);

    $sql = 'select * from  users where id = :id';
    $con = $this->get('pdo');
    $sth = $con->prepare($sql);
    $sth->bindValue(':id', (int)$id, PDO::PARAM_INT);
    $sth->execute();
    $result = $sth->fetch(PDO::FETCH_BOTH);
    $user = $result['name'];

    $sql = 'select count(*) as messages from messages where user_id = :user_id';
    $sth = $con->prepare($sql);
    $sth->bindValue(':user_id', (int)$id, PDO::PARAM_INT);
    $sth->execute();
    $result = $sth->fetch(PDO::FETCH_BOTH);
    $message_count = $result['messages'];

    $sql = 'select count(*) as follow from  follows where user_id = :user_id';
    $sth = $con->prepare($sql);
    $sth->bindValue(':user_id', (int)$id, PDO::PARAM_INT);
    $sth->execute();
    $result = $sth->fetch(PDO::FETCH_BOTH);
    $follow = $result['follow'];

    $sql = 'select count(*) as follower from  follows where follow_user_id = :user_id';
    $sth = $con->prepare($sql);
    $sth->bindValue(':user_id', (int)$id, PDO::PARAM_INT);
    $sth->execute();
    $result = $sth->fetch(PDO::FETCH_BOTH);
    $follower = $result['follower'];

    $sql = 'select * from messages where user_id = :user_id order by created_at desc limit 10';
    $sth = $con->prepare($sql);
    $sth->bindValue(':user_id', (int)$id, PDO::PARAM_INT);
    $sth->execute(array($id));
    $messages = $sth->fetchAll();

    return $this->view->render($response,'chapter2.twig',['user' => $user,'message_count' => $message_count,'follow' => $follow,'follower' => $follower,'messages' => $messages]);
});

$app->get('/1day/chapter3-2/preprocessing',function($request,$response,$args) {
    $con = $this->get('pdo');
    $sql = 'alter table messages add index title_created_at(title, created_at)';
    $sth = $con->prepare($sql);
    $sth->execute();
    echo "The preprocessing of chapter3-2 has been completed!";
});

$app->get('/1day/chapter3-2',function($request,$response,$args) {
    $con = $this->get('pdo');
    $sql = 'select * from messages where title = ? order by created_at desc limit 10';
    $sth = $con->prepare($sql);
    $sth->execute(array($request->getQueryParams()['title']));
    $messages = $sth->fetchAll();
    return $this->view->render($response,'chapter2.twig',['messages' => $messages]);
});

$app->get('/1day/chapter4',function($request,$response,$args) {
    $con = $this->get('pdo');
    $sql = 'truncate table user_birth_month_count';
    $sth = $con->prepare($sql);
    $sth->execute();
    $sql = 'insert into user_birth_month_count(sex, month, count)
            select sex, month(birthday), count(*) from users group by sex, month(birthday)';
    $sth = $con->prepare($sql);
    $sth->execute();
    echo "バッチ処理insert 成功!";
});

$app->get('/1day/chapter5/preprocessing', function($request, $response, $args) {
    $con = $this->get('pdo');
    $sth = $con->prepare('alter table users add index sex_birthday(sex, birthday)');
    $sth->execute();
    $sth = $con->prepare('drop table if exists avg_age');
    $sth->execute();
    $sql = 'create table avg_age (
                select sex, avg(TIMESTAMPDIFF(YEAR, birthday, CURDATE())) AS avg_age from users group by sex
            )';
    $sth = $con->prepare($sql);
    $sth->execute();
    echo "The preprocessing of chapter5 has been completed!";
});

$app->get('/1day/chapter5',function($request,$response,$args) {
    $id = mt_rand(1,100000);

    $con = $this->get('pdo');
    $check = $con->prepare('show tables like "avg_age"');
    $check->execute();
    if($check->rowCount() == 0) {
        echo "You need to access <a href=\"/1day/chapter5/preprocessing\">/1day/chapter5/preprocessing</a>";
        return;
    }

    $message = "キャンペーン中!!";
    $sql = 'select count(*) as cnt from users, avg_age
            where TIMESTAMPDIFF(YEAR, birthday, CURDATE()) > avg_age.avg_age
            and users.sex = avg_age.sex
            and users.id = :id';
    $sth = $con->prepare($sql);
    $sth->bindValue(':id', (int)$id, PDO::PARAM_INT);
    $sth->execute();
    $result = $sth->fetch(PDO::FETCH_BOTH);
    $cnt = $result['cnt'];
    if ($cnt === 0){
        $message = "キャンペーン期間外";
        return $this->view->render($response,'exercise_part8.twig',['title' => 'キャンペーン情報','id' => $id,'message' => $message]);
    }
    return $this->view->render($response,'exercise_part8.twig',['title' => 'キャンペーン情報','id' => $id,'message' => $message]);
});

$app->get('/1day/chapter6',function($request,$response,$args) {
    $con = $this->get('pdo');
    $sql = 'select id from users';
    $sth = $con->prepare($sql);
    $sth->execute();
    $ids = $sth->fetchAll(PDO::FETCH_COLUMN);

    $random_ids = array_rand($ids, 10);

    $query_ids = implode(',', array_fill(0, count($random_ids), '?'));
    $sql = 'select * from users where id in (' . $query_ids . ')';
    $sth = $con->prepare($sql);
    $sth->execute($random_ids);
    $users = $sth->fetchAll();
    return $this->view->render($response,'exercise_part6.twig',['title' => 'オススメユーザ','users' => $users]);
});

$app->get('/1day/chapter7',function($request,$response,$args) {
    $id = mt_rand(1,100000);

    $con = $this->get('pdo');
    $sql = 'select user_id,message,created_at from messages where user_id in (select follow_user_id from follows where user_id = :user_id) order by created_at desc limit 10';
    $sth = $con->prepare($sql);
    $sth->bindValue(':id', (int)$id, PDO::PARAM_INT);
    $sth->execute();
    $time_lines = $sth->fetchAll();

    return $this->view->render($response,'exercise_part7.twig',['title' => $id . 'さんのタイムライン','time_lines' => $time_lines]);
});

$app->get('/1day/chapter8/preprocessing',function($request,$response,$args) {
    $con = $this->get('pdo');
    $check = $con->prepare('show index from users where key_name = "sex_birthday"');
    $check->execute();
    if($check->rowCount() == 0) {
        $sth = $con->prepare('alter table users add index sex_birthday(sex, birthday)');
        $sth->execute();
        echo "The preprocessing of chapter8 has been completed!";
    } else {
        echo "No preprocessing has been executed.";
    }
});

$app->get('/1day/chapter8',function($request,$response,$args) {
    $con = $this->get('pdo');
    $check = $con->prepare('show tables like "avg_age"');
    $check->execute();
    if($check->rowCount() == 0) {
        echo "You need to access <a href=\"/1day/chapter5/preprocessing\">/1day/chapter5/preprocessing</a>";
        return;
    }

    $sql = 'select count(id) as cnt from users, avg_age
            where TIMESTAMPDIFF(YEAR, birthday, CURDATE()) > avg_age.avg_age
            and users.sex = avg_age.sex
            and users.sex = ?';
    $sth = $con->prepare($sql);
    $sth->execute(array(0));
    $result = $sth->fetch(PDO::FETCH_BOTH);
    $cnt = $result['cnt'];
    echo "男性ユーザの平均年齢より高い男性の人数は" . $cnt . "人です";
});
