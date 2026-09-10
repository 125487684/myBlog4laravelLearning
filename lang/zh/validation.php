<?php

return [
    // 规则消息模板：:attribute 会被替换为下方 attributes 里映射的中文名
    'required' => ':attribute不能为空。',
    'confirmed' => ':attribute两次输入不一致。',
    'min' => [
        'string' => ':attribute至少 :min 个字符。',
    ],
    'current_password' => '当前密码不正确。',

    // 表单字段名到中文的映射
    'attributes' => [
        'current_password' => '当前密码',
        'password' => '密码',
        'password_confirmation' => '确认密码',
        'name' => '用户名',
        'email' => '邮箱',
        'title' => '标题',
        'slug' => 'Slug',
        'body' => '正文',
    ],
];
