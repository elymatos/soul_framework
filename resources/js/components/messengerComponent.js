window.messenger = {
    confirmPost(type, message, action) {
        $.messager.confirm({
            cls: "wt-messager wt-messager-" + type,
            title: type.charAt(0).toUpperCase() + type.slice(1),
            msg: message,
            fn: function(r) {
                if (r) {
                    console.log("confirmed: " + r);
                }
            }
        });
    },
    confirmDelete(message, action, onApprove, onDeny) {
        console.log("confirmDelete");
        $.toast({
            title: "Warning",
            message: message + " Confirm?",
            displayTime: 0,
            position: "centered",
            closeOnClick: true,
            actions: [{
                text: "Yes",
                icon: "check",
                class: "green",
                click: async () => {
                    $("body").dimmer("hide");
                    await htmx.ajax("DELETE", action, null);
                    if (typeof onApprove === 'function') {
                        onApprove();
                    }
                }
            }, {
                icon: "ban",
                class: "secondary",
                text: "No",
                click: function() {
                    $("body").dimmer("hide");
                    if (typeof onDeny === 'function') {
                        onDeny();
                    }
                }
            }],
            onShow: function() {
                $("body").dimmer("show");
            }
        });

    },
    notify(type, message) {
        $.toast({
            class: type,
            message: message,
            className: {
                content: "content  wt-notify-" + type
            }
        });
    }
};

document.body.addEventListener("notify", function(evt) {
    console.log("notify");
    $.toast({
        class: evt.detail.type,
        message: evt.detail.message,
        className: {
            content: "content  wt-notify-" + evt.detail.type
        }
    });
});
