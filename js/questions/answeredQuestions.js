import React from "react";

let QuestionRow = require("./questionRow");

let AnsweredQuestions = React.createClass({
    render:function(){
        let rows = [],
            obj = this.props.listAnsweredQuestions,
            visible = this.props.username ? 'block' : 'none';

        if(obj instanceof Object) {
            obj.forEach(function (question) {
                rows.push(<QuestionRow question={question} textButton="Показать" />);
            });
        }
        return(
            <div style={{display:visible}}>
                <h3>Отвеченные вопросы</h3>
                <table>
                    <thead>
                    <tr>
                        <th>№</th>
                        <th>Вопрос</th>
                    </tr>
                    </thead>
                    <tbody>{rows}</tbody>
                </table>
            </div>
        )
    }
});

module.exports = AnsweredQuestions;
