import React from "react";

let QuestionRow = require("./questionRow");

let AllQuestions = React.createClass({
    render:function(){
        let rows = [],
            obj = this.props.listAllQuestions,
            visible = this.props.username ? 'block' : 'none';
        if(obj instanceof Object) {
            obj.forEach(function (question) {
                rows.push(<QuestionRow question={question} />);
            });
        }
        return(
            <div style={{display:visible}}>
                <h3>Все вопросы</h3>
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

module.exports = AllQuestions;
