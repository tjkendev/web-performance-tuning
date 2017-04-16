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
    $sql = '
            insert into user_birth_month_count(sex,month,count)
            select 0,1,count(*) from users where sex =0 and month(birthday) = 1
            union
            select 0,2,count(*) from users where sex =0 and month(birthday) = 2
            union
            select 0,3,count(*) from users where sex =0 and month(birthday) = 3
            union
            select 0,4,count(*) from users where sex =0 and month(birthday) = 4
            union
            select 0,5,count(*) from users where sex =0 and month(birthday) = 5
            union
            select 0,6,count(*) from users where sex =0 and month(birthday) = 6
            union
            select 0,7,count(*) from users where sex =0 and month(birthday) = 7
            union
            select 0,8,count(*) from users where sex =0 and month(birthday) = 8
            union
            select 0,9,count(*) from users where sex =0 and month(birthday) = 9
            union
            select 0,10,count(*) from users where sex =0 and month(birthday) = 10
            union
            select 0,11,count(*) from users where sex =0 and month(birthday) = 11
            union
            select 0,12,count(*) from users where sex =0 and month(birthday) = 12
            union
            select 1,1,count(*) from users where sex =1 and month(birthday) = 1
            union
            select 1,2,count(*) from users where sex =1 and month(birthday) = 2
            union
            select 1,3,count(*) from users where sex =1 and month(birthday) = 3
            union
            select 1,4,count(*) from users where sex =1 and month(birthday) = 4
            union
            select 1,5,count(*) from users where sex =1 and month(birthday) = 5
            union
            select 1,6,count(*) from users where sex =1 and month(birthday) = 6
            union
            select 1,7,count(*) from users where sex =1 and month(birthday) = 7
            union
            select 1,8,count(*) from users where sex =1 and month(birthday) = 8
            union
            select 1,9,count(*) from users where sex =1 and month(birthday) = 9
            union
            select 1,10,count(*) from users where sex =1 and month(birthday) = 10
            union
            select 1,11,count(*) from users where sex =1 and month(birthday) = 11
            union
            select 1,12,count(*) from users where sex =1 and month(birthday) = 12';
    $sth = $con->prepare($sql);
    $sth->execute();
    echo "バッチ処理insert 成功!";
});

$app->get('/1day/chapter5',function($request,$response,$args) {
    $id = mt_rand(1,100000);

    $con = $this->get('pdo');
    $message = "キャンペーン中!!";
    $sql = '
            select count(*) as cnt
            from users a
            where TIMESTAMPDIFF(YEAR,a.birthday,CURDATE()) >
              (select avg(TIMESTAMPDIFF(YEAR,b.birthday,CURDATE())) AS age
               from users b
               where a.sex = b.sex)
            and a.id = :id';
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
    $sql = 'select * from users order by rand() limit 10';
    $sth = $con->prepare($sql);
    $sth->execute();
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

$app->get('/1day/chapter8',function($request,$response,$args) {
    $con = $this->get('pdo');
    $sql = '
            select count(*) as cnt 
            from users a 
            where TIMESTAMPDIFF(YEAR,a.birthday,CURDATE()) >
              (select avg(TIMESTAMPDIFF(YEAR,b.birthday,CURDATE())) AS age
               from users b
               where b.sex = a.sex)
            and a.sex = ?';
    $sth = $con->prepare($sql);
    $sth->execute(array(0));
    $result = $sth->fetch(PDO::FETCH_BOTH);
    $cnt = $result['cnt'];
    echo "男性ユーザの平均年齢より高い男性の人数は" . $cnt . "人です";
});
