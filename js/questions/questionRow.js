/**
 * Формирование строк таблицы с вопросами
 */

import React from "react";

let ForAnswerBlock = React.createClass({
    handlerPublicAnswer: function(event){
        let form = event.target;
        this.props.onSubmit(event,form);
    },
   render:function(){
       if(this.props.show) {
           return (
               <form method="post" onSubmit={this.handlerPublicAnswer} className="display-none">
                   <input type="hidden" name="action" value="answer"/>
                   <input type="hidden" name="username" value={this.props.username}/>
                   <input type="hidden" name="question_id" value={this.props.questionId}/>
                   <table>
                   <tr>
                       <td colSpan="3">
                           <textarea name="text" cols="100" rows="10" placeholder="Введите ваш ответ здесь"></textarea>
                       </td>
                       <td>
                           <button type="submit">Опубликовать ответ</button>
                       </td>
                   </tr>
                   </table>
               </form>
           )
       }else{
           return null;
       }
   }
});

let QuestionRow = React.createClass({
    handlerTryAnswer:function(event){
        event.preventDefault();
        let block = $(event.target).parent().parent().next(),
            button = $(event.target);
        block.toggle();
        if( block.is(":visible")){
            button.text("Отменить")
        }else{
            button.text("Ответить")
        }
    },
    render: function() {
        let visible = this.props.textButton ? "block" : "none",
            notAnswered = this.props.notAnswered;
        return (
            <thead>
                <tr>
                    <td>{this.props.question['id']}</td>
                    <td>{this.props.question['text']}</td>
                    <td style={{display:visible}}><button onClick={this.handlerTryAnswer}>{this.props.textButton}</button></td>
                </tr>
                <ForAnswerBlock
                    show={notAnswered}
                    onSubmit={this.props.onSubmit}
                    username={this.props.username}
                    questionId={this.props.question['id']}
                />
            </thead>
        );
    }
});

module.exports = QuestionRow;