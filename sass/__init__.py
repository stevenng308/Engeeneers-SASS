import sqlite3
from flask import Flask, request, render_template, g
from wtforms import Form, BooleanField, TextField, PasswordField, validators
from flask.ext.sqlalchemy import SQLAlchemy

# Configuration
DATABASE = '/tmp/mh4u.db'

# Create the application
app = Flask(__name__)
app.config.from_object(__name__)

def connect_to_database():
    return sqlite3.connect(app.config['DATABASE'])

def get_db():
    db = getattr(g, '_database', None)
    if db is None:
        db = g._database = connect_to_database()
    return db

def query_db(query, args=(), one=False):
    cur = get_db().execute(query, args)
    rv = cur.fetchall()
    cur.close()
    return (rv[0] if rv else None) if one else rv

@app.teardown_appcontext
def close_connection(exception):
    db = getattr(g, '_database', None)
    if db is not None:
        db.close()

@app.route('/')
def index():
    return "<html><head></head><body><h1>Hello World!</h1></body></html>"

@app.route('/login', methods=['GET', 'POST'])
def login():
    db = get_db()
    error = None
    if request.method == 'POST':
        if valid_login(
            request.form['username'],
            request.form['password']):
            return log_the_user_in(request.form['username'])
        else:
            error = 'Invalid username/password'
    return render_template('login.html', error=error)

@app.route('/welcome/')
@app.route('/welcome/<name>')
def welcome(name=None):
    return render_template('welcome.html', name=name)
    

if __name__ == "__main__":
    app.run(debug=True)
