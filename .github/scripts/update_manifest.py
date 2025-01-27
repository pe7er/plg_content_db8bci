import sys
import xml.etree.ElementTree as ET
from datetime import datetime

def update_manifest(manifest_path, version):
    # Define the current date
    current_date = datetime.now().strftime('%Y-%m-%d')

    # Load the XML file
    tree = ET.parse(manifest_path)
    root = tree.getroot()

    # Update the creationDate
    date_element = root.find('.//creationDate')
    if date_element is not None:
        date_element.text = current_date

    # Update the version
    version_element = root.find('.//version')
    if version_element is not None:
        version_element.text = version

    # Save the updated manifest
    tree.write(manifest_path)

if __name__ == "__main__":
    if len(sys.argv) != 3:
        print("Usage: update_manifest.py <manifest_path> <version>")
        sys.exit(1)

    manifest_path = sys.argv[1]
    version = sys.argv[2]
    update_manifest(manifest_path, version)
