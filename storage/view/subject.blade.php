<html>
<head>
    <title>subject</title>
    <meta charset="utf-8"/>
    <link rel="stylesheet" href="/layui/css/layui.css">
</head>
<body>
<div class="layui-container">
    @foreach ($subjects as $subject)
        <div class="layui-row">
            <div class="layui-row">
                {{ $subject->number }}
            </div>
            <div class="layui-row">
                {{ $subject->content->title }}
            </div>
            <div class="layui-row layui-col-space8">
                @foreach ($subject->content->images_content as $image)
                    <div class="layui-col-xs2">
                        <img src="{{ $image }}" style="width: 100%"/>
                    </div>
                @endforeach
            </div>
        </div>
    @endforeach
</div>
<script src="/layui/layui.js"></script>
</body>
</html>