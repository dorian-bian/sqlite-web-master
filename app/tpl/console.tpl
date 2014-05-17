<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8" />
    <title>Console</title>
    <link rel="stylesheet" type="text/css" href="./res/main.css" />
    <style type="text/css">html,body { padding-top: 20px; background-color:#F6F6F6; }</style>
</head>
<body>
{+when:no a=debug}
    <div class="error-item">
        <h1>Error</h1>
        <div>
            <ul>
                <li>
                    <p>Sorry, There're some bad things happened when you do this operation. Please connect admin to fixed it.</p>
                </li>
            </ul>
        </div>
    </div>
{-when:no}
{+when:ok a=debug}
    {+alerts}
    <div class="alert-item">
        <h1>{type}</h1>
        <div>
            <pre class="data"><em>{dump encode=no}</em></pre>
        </div>
    </div>
    {-alerts}
    {+errors}
    <div class="error-item">
        <h1>{info}</h1>
        <div>
        {+when:ok a=data}
            <pre><em>{data}</em></pre>
        {-when:ok}
            <ul>
        {+foot}
                <li>
                    <span>{file}</span>
                    <pre><label>{line}</label><code>{code}</code></pre>
                </li>
        {-foot}
            </ul>
        </div>
    </div>
    {-errors}
{-when:ok}
</body>
</html>
