@extends('panel.base')
@section('title','AlphaBot - '.__('chatbot.title'))
@section('breadcrumb_link')
    <ol class="breadcrumb float-sm-right">
        <li class="breadcrumb-item"><a href="{{route('admin.index')}}">@lang('dashboard.dashboard')</a></li>
        <li class="breadcrumb-item active">AlphaBot</li>
    </ol>
@endsection
@section('content')
<div class="row">
    <div class="col-sm-11 col-md-11 col-lg-11 container px-4">
        <div class="card">
            <div id="chat-list" class="card-body" style="height: 600px; overflow-y: auto;">
            </div>
        </div>
    </div>
</div>
<div class="row">
    <div class="col-sm-11 col-md-11 col-lg-11 container py-2 px-4">
        <div class="card">
            <div class="card-body">
                <div class="input-group input-group-flat">
                    <textarea rows="1" id="input-message"
                              class="form-control"
                              autocomplete="off"
                              aria-label="@lang('chatbot.type_your_message')"
                              placeholder="@lang('chatbot.type_your_message')"></textarea>
                    <button id="btn-send" class="btn">@lang('chatbot.send')</button>
                </div>
            </div>
        </div>
    </div>
</div>
<div class="relative">
    <div style="position: absolute; bottom: 10px; left:0; right: 0;">

    </div>
</div>
@endsection

@section("script")
<script src="https://unpkg.com/marked@12.0.1/marked.min.js"></script>
<script>
    let stillWriting = false;

    const btnSend = document.getElementById("btn-send");
    const chatListEl = document.getElementById("chat-list");
    const inputMessage = document.getElementById("input-message");

    const createElementFromStr = (str) => {
        const div = document.createElement('div');
        div.innerHTML = str;
        return div;
    }

    const createElementChatItem = (type) => {
        const chatItemElementTemplateBot = `
            <div class="d-flex p-2 rounded mb-2" style="background-color: #f3f4f6">
                <div class="mb-1">
                    <img src="{{config("app.url")}}/themes/panel/img/chatbot.png"
                    class="img-circle elevation-2 img-fluid"
                    height="80"
                    width="80"
                    style="object-fit: cover;"
                    alt="ChatBot">
                </div>
                <div class="px-3">
                    <div class="text-muted">AlpaBot</div>
                    <div id="message-content"></div>
                    <div id="scroll-item"></div>
                </div>
            </div>`;
        const chatItemElementTemplateUser = `
            <div class="d-flex p-2 rounded mb-2" style="background-color: #f3f4f6; ">
                <div class="mb-1">
                    <img src="https://www.gravatar.com/avatar/{{md5(strtolower(trim(auth()->user()->email)))}}"
                    class="img-circle elevation-2 img-fluid"
                    height="80"
                    width="80"
                    style="object-fit: cover;"
                    alt="{{auth()->user()->nickname}}">
                </div>
                <div class="px-3">
                    <div class="text-muted">{{auth()->user()->nickname}}</div>
                    <div id="message-content"></div>
                </div>
            </div>`;

        let chatItemElementTemplate = chatItemElementTemplateBot;
        if (type === "user") {
            chatItemElementTemplate = chatItemElementTemplateUser;
        }

        const newElement = createElementFromStr(chatItemElementTemplate)
        newElement.className = "chat-item";
        return newElement;
    }

    const createErrorElement = (message) => {
        const templete = `
            <div class="d-flex p-2 rounded mb-2" style="background-color: #f3f4f6">
                <img src="{{config("app.url")}}/themes/panel/img/chatbot.png"
                    class="img-circle elevation-2 img-fluid"
                    height="80"
                    width="80"
                    style="object-fit: cover;"
                    alt="ChatBot">
                <div class="px-3">
                    <div class="text-muted mb-2">System</div>
                    <div class="alert alert-danger bg-white" role="alert">
                        <div class="d-flex">
                            <svg xmlns="http://www.w3.org/2000/svg" class="icon alert-icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"></path><path d="M3 12a9 9 0 1 0 18 0a9 9 0 0 0 -18 0"></path><path d="M12 8v4"></path><path d="M12 16h.01"></path></svg>
                            <div>${message}</div>
                        </div>
                    </div>
                </div>
            </div>`;
        const newElement = createElementFromStr(templete);
        newElement.innerHTML = templete;
        return newElement;
    }

    const triggerStreaming = (question) => {
        stillWriting = true;
        const newBotElement = createElementChatItem();
        const newUserElement = createElementChatItem("user");
        chatListEl.appendChild(newUserElement);
        const messageItemUser = newUserElement.querySelector("#message-content");
        messageItemUser.innerText = question;

        const queryQuestion = encodeURIComponent(question);
        let url = `{{route('alphabot.streaming')}}?question=${queryQuestion}`;
        const source = new EventSource(url);
        let sseText = "";

        chatListEl.appendChild(newBotElement);
        const messageContent = newBotElement.querySelector("#message-content");
        const scrollItem = newBotElement.querySelector("#scroll-item");
        scrollItem.scrollIntoView();

        source.addEventListener("update", (event) => {
            if (event.data === "<END_STREAMING_SSE>") {
                source.close();
                stillWriting = false;
                return;
            }

            const data = JSON.parse(event.data);
            if (data.text) {
                sseText += data.text;
                messageContent.innerHTML = marked.parse(sseText);
            }
            console.log(data);
            scrollItem.scrollIntoView();
        });

        source.addEventListener("error", (event) => {
            stillWriting = false;
            console.error('EventSource failed:', event);
            newBotElement.remove();
            newUserElement.remove();
            const errorEl = createErrorElement("An error occurred. Try again later.")
            chatListEl.appendChild(errorEl);
        })
    };

    function submitSendMessage() {
        if (stillWriting) {
            return;
        }

        const inputText = inputMessage.value;
        const btnRetry = document.getElementById("btn-retry");
        if (inputText != "") {
            if (btnRetry) {
                btnRetry.remove();
            }
            inputMessage.value = "";
            triggerStreaming(inputText);
        } else {
            inputText.focus();
        }
    }

    btnSend.addEventListener("click", () => {
        submitSendMessage()
    })

    inputMessage.addEventListener("keyup", function(event) {
        if (event.keyCode === 13) {
            event.preventDefault();
            submitSendMessage();
        }
    });


</script>
@endsection