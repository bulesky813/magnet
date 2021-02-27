<!DOCTYPE html>
<html>
<head>
    <title>subject</title>
    <meta charset="utf-8"/>
    <link rel="stylesheet" href="/layui/css/layui.css">
</head>
<body>
<div class="layui-container">
    <div class="layui-row">
        <div class="layui-col-xs-12">
            <h1>待处理的番号</h1>
            <hr/>
        </div>
    </div>
    @foreach ($subjects as $subject)
        <div class="layui-row">
            <div class="layui-row" style="padding: 15px 0px;">
                <div class="layui-col-xs3"><h2>{{ $subject->number }}</h2></div>
                <div class="layui-col-xs9">
                    <button class="layui-btn layui-btn-xs" onclick="process('{{ $subject->number }}')">已处理</button>
                </div>
            </div>
            <div class="layui-row">
                <blockquote class="layui-elem-quote">
                    <p>标题：{{ $subject->content->title }}</p>
                    <p>评分：{{ $subject->content->rating }}</p>
                    <p>演员：
                        @foreach($subject->content->casts as $casts)
                            <span class="layui-badge layui-bg-blue"><a
                                        href="{{ $casts->url }}">{{ $casts->name }}</a></span>
                        @endforeach
                    </p>
                </blockquote>
            </div>
            <div class="layui-row layui-col-space8">
                @foreach ($subject->content->images_content as $image)
                    <div class="layui-col-xs2">
                        <img layer-src="{{ $image }}" lay-src="{{ $image }}" style="width: 100%"
                             class="images-content"/>
                    </div>
                @endforeach
            </div>
            <hr>
        </div>
    @endforeach
</div>
<script src="/layui/layui.js"></script>
<script type="text/javascript">
    var $;
    layui.use(['flow', 'layer', 'jquery'], function () {
        var flow = layui.flow;
        var layer = layui.layer;
        $ = layui.$;
        flow.lazyimg();
        $(".images-content").on("click", function (element) {
            layer.open({
                type: 1,
                title: false,
                closeBtn: 0,
                area: ['auto'],
                skin: 'layui-layer-lan', //没有背景色
                shadeClose: true,
                content: '<div><img src="' + $(this).attr("layer-src") + '" /></div>'
            });
        });
    });

    function process(number) {
        $.post({
            type: 'POST',
            url: '/javdb/v1/ajax/process/subject',
            data: {
                number: number
            },
            success: function (data) {
                window.location.reload();
            }
        });
    }
</script>
</body>
</html>