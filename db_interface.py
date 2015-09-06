from peewee import *

db = PostgresqlDatabase('fys_database', user='customer')

class BaseModel(Model):
    """A base model that will use the fys database"""
    class Meta:
        database = db

class User(BaseModel):
    username = CharField()


