import React from "react";

let QuestionRow = require("./questionRow");

let NotAnsweredQuestions = React.createClass({
    render:function(){
        let rows = [],
            obj = this.props.listNotAnsweredQuestions,
            visible = this.props.username ? 'block' : 'none';

        if(obj instanceof Object) {
            obj.forEach(function (question) {
                rows.push(<QuestionRow
                    question={question}
                    textButton="Показать" />
                );
            });
        }
        return(
            <div style={{display:visible}}>
                <h3>Неотвеченные вопросы</h3>
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

module.exports = NotAnsweredQuestions;
