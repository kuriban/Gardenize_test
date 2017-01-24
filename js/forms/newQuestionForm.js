import React from "react";

let newQuestionForm = React.createClass({
    handerSubmit: function(event){
        let form = event.target;
        this.props.onSubmit(event,form);
    },
    render: function(){
        let visible = this.props.username ? 'block' : 'none';

        return (
            <form method="post" onSubmit={this.handerSubmit} style={{display:visible}}>
                <input type="hidden" name="action" value="writeNewQuestion"/>
                <input type="hidden" name="username" value={this.props.username}/>
                <textarea cols="100" rows="10" placeholder="Новый вопрос" name="text"></textarea>
                <br />
                <input type="submit" value="Задать вопрос" />
            </form>
        )
    }
});

module.exports = newQuestionForm;