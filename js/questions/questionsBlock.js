/**
 * Отображение списка вопросов.
 * Props - textButton - текс на кнопке (Показать/Ответить), question - Объект со списком вопросо
 */
import React from "react";

let QuestionRow = require("./questionRow");

let QuestionsBlock = React.createClass({
    render:function(){
        let rows = [],
            obj = this.props.listQuestions,
            visible = this.props.username ? 'block' : 'none';
        if(obj instanceof Object) {
            obj.forEach(function (question) {
                rows.push(<QuestionRow
                    question={question}
                    textButton={this.props.textButton}
                    notAnswered={this.props.notAnswered}
                    onSubmit={this.props.onSubmit}
                    username={this.props.username}
                />
                );
            }.bind(this));
        }
        return(
            <div style={{display:visible}}>
                <h3>{this.props.text}</h3>
                <table>
                    <thead>
                    <tr>
                        <th>№</th>
                        <th>Вопрос</th>
                    </tr>
                    </thead>
                    {rows}
                </table>
            </div>
        )
    }
});

module.exports = QuestionsBlock;
