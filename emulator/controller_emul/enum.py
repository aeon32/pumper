# -*- coding:utf-8 -*-
def enum(**enums):
    """

    :rtype: object
    """
    return type('Enum', (), enums)


class EnumNamedSuperClass:

    _values_dict = {}

    @classmethod
    def __len__(cls):
        return len(cls._values_dict)

    @classmethod
    def __getitem__(cls, key):
        return cls._values_dict.__getitem__(key)

    @classmethod
    def __contains__(cls, key):
        return key in cls._values_dict


def enum_named(**enums) :
    nonamed_enums = { key:value[0] for (key,value) in enums.items()}
    enum_class = type('Enum', (EnumNamedSuperClass,), nonamed_enums)

    enum_class._values_dict = {value[0]:value[1] for (key, value) in enums.items()}
    return enum_class()




