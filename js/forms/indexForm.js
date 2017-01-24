import React from "react";

let IndexForm = React.createClass({
    handerSubmit: function(event){
        let form = event.target;
        this.props.onSubmit(event,form);
    },
    render: function(){
        return (
            <form method="post" onSubmit={this.handerSubmit}>
                <input type="hidden" name="action" value="getUserData"/>
                <input type="text" name="username" required placeholder="введите имя пользователя" />
                <input type="submit" value="Показать данные" />
            </form>
        )
    }
});

module.exports = IndexForm;