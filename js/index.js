import React from "react";
import ReactDOM from "react-dom";

import {Router, Route, browserHistory}from "react-router";

let IndexForm       = require("./forms/indexForm");
let NewQuestionForm = require("./forms/newQuestionForm");

let QuestionsBlock    = require("./questions/questionsBlock");

class IndexPage extends React.Component {
    constructor(props) {
        super(props);
        this.state = {
            username: '',
            listAllQuestions: '',
            listAnsweredQuestions: '',
            listNotAnsweredQuestions: ''
        };
        this.getUserData = this.getUserData.bind(this);
    }
    responses(response){
        let username = response.username,
            listAllQuestions     = response.listAllQuestions,
            listAnsweredQuestions   = response.listAnsweredQuestions,
            listNotAnsweredQuestions   = response.listNotAnsweredQuestions;

        if(username !== undefined){
            this.setState({username:username})
        }
        if(listAllQuestions !== undefined){
            this.setState({listAllQuestions:listAllQuestions})
        }
        if(listNotAnsweredQuestions !== undefined){
            this.setState({listNotAnsweredQuestions:listNotAnsweredQuestions})
        }
        if(listAnsweredQuestions !== undefined){
            this.setState({listAnsweredQuestions:listAnsweredQuestions})
        }
    }

    getUserData(event,form) {
        event.preventDefault();
        let formData = new FormData(form),
            action = "/ajax/get-set-data.php";

        fetch(action,{
            credentials:'same-origin',
            method:"POST",
            body:formData
        }).then(response=>{
            if(response.status>=200 && response.status<300){
                return response.json();
            }else{
                Promise.reject();
            }
        }).then(json=>{
            this.responses(json);
        }).catch(e=>{
            alert(e)
        });
    }

    render() {
        return (
            <div>
                <IndexForm onSubmit={this.getUserData.bind(this)} />
                <QuestionsBlock
                    listQuestions={this.state.listAllQuestions}
                    username={this.state.username}
                    text="Все вопросы"/>
                <QuestionsBlock
                    listQuestions={this.state.listAnsweredQuestions}
                    username={this.state.username}
                    text="Все отвеченные вопросы"
                    textButton="Показать"
                />
                <QuestionsBlock
                    listQuestions={this.state.listNotAnsweredQuestions}
                    username={this.state.username}
                    text="Все неотвеченные вопросы"
                    textButton="Ответить"
                    notAnswered={1}
                    onSubmit={this.getUserData.bind(this)}
                />

                <div style={{marginTop:20}}>
                    <NewQuestionForm onSubmit={this.getUserData.bind(this)} username={this.state.username} />
                </div>
            </div>
        );
    }
}


ReactDOM.render(
    <IndexPage />,
    document.getElementById("main")
);
